# Functional Requirements Specification

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Address Management

### FR-001: Address Value Object
The system SHALL represent addresses as immutable value objects containing: name, address line 1, address line 2 (optional), city/municipality, province/state, postal/zip code, and country.

### FR-002: Address Validation
The system SHALL validate that required address fields (name, address line 1, city, province, postal code) are present before formatting.

### FR-003: Canadian Postal Code Validation
The system SHALL validate Canadian postal codes match the pattern `A1A 1A1` (letter-digit-letter space digit-letter-digit).

---

## 2. Address Formatting

### FR-010: Canada Post Formatting
The system SHALL format Canadian addresses per Canada Post T601 standard:
- All uppercase characters
- No punctuation except in proper names
- Municipality, province (2-letter code), and postal code on same line
- One space between municipality and province
- Two spaces between province and postal code
- Maximum 40 characters per line (excluding spaces)
- 3 to 6 address lines
- No `#` symbol
- No "CANADA" on domestic addresses

### FR-011: International Formatting
The system SHALL append the country name (full, uppercase) as the last line for international addresses.

### FR-012: Formatter Extensibility
The system SHALL support pluggable address formatters via `AddressFormatterInterface`, allowing new carrier/national standards to be added without modifying existing code.

---

## 3. Contact Type Management

### FR-020: Customer Contact Support
The system SHALL retrieve customer addresses from FA `debtors_master` and `cust_branch` tables, supporting both billing and shipping addresses.

### FR-021: Supplier Contact Support
The system SHALL retrieve supplier addresses from FA `suppliers` table.

### FR-022: Employee Contact Stub
The system SHALL provide an `EmployeeContact` stub that implements `ContactTypeInterface` but throws a "not yet supported" exception, for future extension.

### FR-023: Company Address
The system SHALL retrieve the company return address from FA company settings (`sys_prefs` table).

### FR-024: Contact Listing
The system SHALL provide a list of available contacts (name + ID) for the selected contact type, for UI population.

### FR-025: Branch Listing
The system SHALL provide a list of branches/destinations for a selected contact, where applicable (e.g., customer branches).

---

## 4. Label Type Selection

### FR-030: Company-Only Label
The system SHALL generate labels containing only the company's address.

### FR-031: Contact-Only Label
The system SHALL generate labels containing only the selected contact's address.

### FR-032: Paired Label
The system SHALL generate labels containing both the company return address and the contact destination address, positioned per postal standards.

---

## 5. Output Plugins

### FR-040: Plugin Architecture
The system SHALL support pluggable output formats via `OutputPluginInterface`. Each plugin provides its own format list and rendering logic.

### FR-041: Envelope/Sheet Plugin
The system SHALL provide an Envelope/Sheet plugin supporting direct print on:
- #10 Envelope (241×105mm)
- DL Envelope (220×110mm)
- C5 Envelope (229×162mm)
- C4 Envelope (324×229mm)
- 6×9 Envelope (229×152mm)
- 9×12 Envelope (305×229mm)
- Letter Paper (216×279mm)
- A4 Paper (210×297mm)

### FR-042: Avery Label Sheet Plugin
The system SHALL provide an Avery Label Sheet plugin supporting:
- Avery 5160: 1″×2⅝″, 30 labels/sheet (Letter)
- Avery 5163: 2″×4″, 10 labels/sheet (Letter)
- Avery L7160: 21.2mm×63.5mm, 21 labels/sheet (A4)
- Avery L7163: 38.1mm×99.1mm, 14 labels/sheet (A4)

### FR-043: Thermal Printer Plugin
The system SHALL provide a Thermal Printer plugin supporting:
- 4×6 Shipping (152×102mm)
- 2¼×1¼ Product (57×32mm)
- 2⁵⁄₁₆×4 Dymo (59×102mm)

### FR-044: Plugin Registration
The system SHALL provide a plugin registry that discovers and registers output plugins at module load time.

---

## 6. Label Layout

### FR-050: Return Address Positioning
For paired labels, the return address block SHALL be positioned in the top-left area of the medium, inset approximately 15mm from top and left edges.

### FR-051: Destination Address Positioning
For paired labels, the destination address block SHALL be positioned in the center-to-lower-center area of the medium.

### FR-052: Font Sizing
Address text SHALL use a font size that produces characters between 2mm and 5mm height per Canada Post guidelines. Return address font SHALL NOT exceed destination address font size.

### FR-053: Layout Adaptation
The layout engine SHALL adapt address block positions proportionally for different medium sizes.

---

## 7. PDF Generation

### FR-060: PDF Output
The system SHALL generate PDF output using the TCPDF library.

### FR-061: Custom Page Size
The PDF page size SHALL match the selected output medium dimensions.

### FR-062: Browser Streaming
The system SHALL stream the generated PDF directly to the user's browser for viewing, printing, or saving.

---

## 8. User Interface

### FR-070: Report Menu Integration
The module SHALL appear in FA's Reports menu under a "Shipping Labels" entry.

### FR-071: Label Generation Form
The form SHALL present selections in this order:
1. Label Type (Company / Contact / Paired)
2. Contact Type (if applicable)
3. Contact selector (if applicable)
4. Branch selector (if applicable)
5. Output Plugin
6. Output Format (populated from selected plugin)
7. Generate button

### FR-072: Dynamic Form Updates
The form SHALL dynamically show/hide fields based on label type selection (e.g., hide contact fields for Company-Only).

### FR-073: Non-Numeric Report Registration
The system SHALL register the module report using alphanumeric identifiers, avoiding the hardcoded numeric ID requirement where possible in FrontAccounting.
