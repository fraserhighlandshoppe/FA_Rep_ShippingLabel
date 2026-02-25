# System Architecture Document

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Architecture Overview

The module follows a **layered architecture** within FrontAccounting's module system, with a **plugin architecture** for output formats and a **Strategy pattern** for address formatting.

### 1.1 Layered Architecture

```mermaid
graph TB
    subgraph "Presentation Layer"
        REP["shipping_labels.php<br/>Report UI Form"]
    end

    subgraph "Business Logic Layer"
        LT["LabelType"]
        LLE["LabelLayout Engine"]
        AF["Address Formatters<br/>(Strategy)"]
        PR["Output Plugin Registry"]
    end

    subgraph "Data Access Layer"
        CT["ContactType<br/>Implementations"]
        ADDR["Address<br/>Value Object"]
    end

    subgraph "Infrastructure Layer"
        REN["TcpdfRenderer"]
        FA["FA Core<br/>(DB, TCPDF, Session)"]
    end

    REP --> LT
    REP --> PR
    REP --> CT
    CT --> ADDR
    LT --> LLE
    LLE --> AF
    PR --> REN
    REN --> FA
    CT --> FA
```

---

## 2. Component Diagram

```mermaid
graph LR
    subgraph "Address Domain"
        A["Address<br/>(Value Object)"]
        AFI["AddressFormatter<br/>Interface"]
        CPF["CanadaPost<br/>Formatter"]
        GF["Generic<br/>Formatter"]
        AFI --> CPF
        AFI --> GF
    end

    subgraph "Contact Types"
        CTI["ContactType<br/>Interface"]
        CC["Customer<br/>Contact"]
        SC["Supplier<br/>Contact"]
        EC["Employee<br/>Contact"]
        CTI --> CC
        CTI --> SC
        CTI --> EC
    end

    subgraph "Label System"
        LM["LabelMedium"]
        LLI["LabelLayout<br/>Interface"]
        LL["LabelLayout"]
        EL["Envelope<br/>Layout"]
        LTYPE["LabelType<br/>Enum"]
        LLI --> LL
        LLI --> EL
    end

    subgraph "Plugin System"
        OPI["OutputPlugin<br/>Interface"]
        OPR["OutputPlugin<br/>Registry"]
        EP["Envelope<br/>Plugin"]
        AP["Avery<br/>Plugin"]
        TP["Thermal<br/>Plugin"]
        OPI --> EP
        OPI --> AP
        OPI --> TP
        OPR --> OPI
    end

    subgraph "Renderer"
        REN["Tcpdf<br/>Renderer"]
    end

    CC --> A
    SC --> A
    EP --> LL
    AP --> LL
    TP --> LL
    EP --> REN
    AP --> REN
    TP --> REN
    LL --> AFI
```

---

## 3. Class Diagram (Core)

```mermaid
classDiagram
    class Address {
        -string name
        -string addressLine1
        -string addressLine2
        -string city
        -string province
        -string postalCode
        -string country
        +getName() string
        +getAddressLine1() string
        +getCity() string
        +getProvince() string
        +getPostalCode() string
        +getCountry() string
        +toArray() array
        +fromArray(array) Address$
    }

    class AddressFormatterInterface {
        <<interface>>
        +formatForLabel(Address) array
        +getMinCharHeightMm() float
        +getMaxCharHeightMm() float
        +getMaxLineLength() int
    }

    class CanadaPostFormatter {
        +formatForLabel(Address) array
        +getMinCharHeightMm() float
        +getMaxCharHeightMm() float
        +getMaxLineLength() int
        -formatPostalCode(string) string
        -getProvinceAbbr(string) string
    }

    class ContactTypeInterface {
        <<interface>>
        +getTypeName() string
        +listContacts() array
        +listBranches(int) array
        +getAddress(int, int) Address
        +getCompanyReturnAddress() Address
    }

    class OutputPluginInterface {
        <<interface>>
        +getName() string
        +getOutputFormats() array
        +render(LabelType, array, string) string
        +supportsLabelType(LabelType) bool
    }

    class LabelRendererInterface {
        <<interface>>
        +render(LabelLayout, array, array) string
    }

    AddressFormatterInterface <|.. CanadaPostFormatter
    AddressFormatterInterface <|.. GenericFormatter
    ContactTypeInterface <|.. CustomerContact
    ContactTypeInterface <|.. SupplierContact
    ContactTypeInterface <|.. EmployeeContact
    OutputPluginInterface <|.. EnvelopeOutputPlugin
    OutputPluginInterface <|.. AveryOutputPlugin
    OutputPluginInterface <|.. ThermalOutputPlugin
    LabelRendererInterface <|.. TcpdfRenderer
```

---

## 4. Sequence Diagram — Generate Paired Envelope Label

```mermaid
sequenceDiagram
    actor User
    participant UI as shipping_labels.php
    participant CT as CustomerContact
    participant AF as CanadaPostFormatter
    participant LL as EnvelopeLayout
    participant EP as EnvelopePlugin
    participant REN as TcpdfRenderer

    User->>UI: Select Paired / Customer / Contact / #10 Envelope
    UI->>CT: getCompanyReturnAddress()
    CT-->>UI: Address (company)
    UI->>CT: getAddress(contactId, branchId)
    CT-->>UI: Address (contact)
    UI->>AF: formatForLabel(companyAddress)
    AF-->>UI: returnLines[]
    UI->>AF: formatForLabel(contactAddress)
    AF-->>UI: destLines[]
    UI->>EP: render(PAIRED, addresses, "#10 Envelope")
    EP->>LL: computeLayout("#10 Envelope")
    LL-->>EP: positions
    EP->>REN: render(layout, returnLines, destLines)
    REN-->>EP: PDF bytes
    EP-->>UI: PDF bytes
    UI-->>User: Stream PDF to browser
```

---

## 5. Design Patterns Used

| Pattern | Where | Purpose |
|---------|-------|---------|
| Strategy | `AddressFormatterInterface` | Swap carrier formatting without changing clients |
| Plugin | `OutputPluginInterface` | Different output targets without modifying core |
| Factory | `OutputPluginRegistry` | Create/discover plugins dynamically |
| Value Object | `Address` | Immutable, side-effect-free address data |
| Template Method | `LabelLayout` → `EnvelopeLayout` | Base layout with envelope-specific overrides |
| Composition | Contact types compose `Address` | Prefer composition over inheritance |

---

## 6. Technology Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 7.3+ |
| PDF | TCPDF |
| Database | MySQL/MariaDB via FA DAL |
| Autoloading | PSR-4 via Composer |
| Testing | PHPUnit |
| Module System | FA modules directory + hooks |
