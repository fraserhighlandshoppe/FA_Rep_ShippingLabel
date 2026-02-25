# Requirements Traceability Matrix

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## Business Requirements → Functional Requirements

| Business Req | Description | Functional Reqs |
|-------------|-------------|-----------------|
| BR-001 | Reduce manual addressing time by 90% | FR-020, FR-021, FR-023, FR-024, FR-025, FR-060, FR-062, FR-073 |
| BR-002 | 100% Canada Post T601 compliance | FR-010, FR-011, FR-012, FR-050, FR-052 |
| BR-003 | Multiple output media support | FR-040, FR-041, FR-042, FR-043, FR-044 |
| BR-004 | Leverage existing FA address data | FR-020, FR-021, FR-023, FR-024, FR-025 |
| BR-005 | Extensible architecture | FR-012, FR-040, FR-044 |

---

## Functional Requirements → Design → Code → Tests

| Req ID | Description | Component | Source File(s) | Test Case(s) |
|--------|-------------|-----------|---------------|-------------|
| FR-001 | Address value object | Address | `src/Address/Address.php` | TC-001–TC-007 |
| FR-002 | Address validation | Address | `src/Address/Address.php` | TC-003 |
| FR-003 | Canadian postal code validation | Address | `src/Address/Address.php` | TC-004, TC-005 |
| FR-010 | Canada Post formatting | Formatter | `src/Address/CanadaPostFormatter.php` | TC-010–TC-019 |
| FR-011 | International formatting | Formatter | `src/Address/GenericFormatter.php` | TC-020 |
| FR-012 | Formatter extensibility | Formatter | `src/Address/AddressFormatterInterface.php` | — |
| FR-020 | Customer contact support | ContactType | `src/ContactType/CustomerContact.php` | TC-070, TC-071 |
| FR-021 | Supplier contact support | ContactType | `src/ContactType/SupplierContact.php` | TC-072 |
| FR-022 | Employee contact stub | ContactType | `src/ContactType/EmployeeContact.php` | TC-073 |
| FR-023 | Company address | ContactType | `src/ContactType/CustomerContact.php` | TC-074 |
| FR-024 | Contact listing | ContactType | `ContactTypeInterface.php` | TC-070 |
| FR-025 | Branch listing | ContactType | `ContactTypeInterface.php` | TC-071 |
| FR-030 | Company-only label | Label | `src/Label/LabelType.php` | TC-103 |
| FR-031 | Contact-only label | Label | `src/Label/LabelType.php` | TC-104 |
| FR-032 | Paired label | Label | `src/Label/LabelType.php` | TC-105 |
| FR-040 | Plugin architecture | Plugin | `src/Plugin/OutputPluginInterface.php` | — |
| FR-041 | Envelope/Sheet plugin | Plugin | `src/Plugin/EnvelopePlugin/` | TC-030–TC-032, TC-100 |
| FR-042 | Avery plugin | Plugin | `src/Plugin/AveryPlugin/` | TC-050–TC-054, TC-101 |
| FR-043 | Thermal plugin | Plugin | `src/Plugin/ThermalPlugin/` | TC-060–TC-062, TC-102 |
| FR-044 | Plugin registration | Plugin | `src/Plugin/OutputPluginRegistry.php` | — |
| FR-050 | Return address position | Layout | `src/Label/LabelLayout.php` | TC-040 |
| FR-051 | Destination address position | Layout | `src/Label/LabelLayout.php` | TC-041 |
| FR-052 | Font sizing | Layout | `src/Label/LabelLayout.php` | TC-042 |
| FR-053 | Layout adaptation | Layout | `src/Label/LabelLayout.php` | TC-043, TC-044 |
| FR-060 | PDF output | Renderer | `src/Renderer/TcpdfRenderer.php` | TC-100–TC-102 |
| FR-061 | Custom page size | Renderer | `src/Renderer/TcpdfRenderer.php` | TC-100 |
| FR-062 | Browser streaming | FA Integration | `reporting/shipping_labels.php` | — |
| FR-070 | Report menu integration | FA Integration | `hooks.php` | — |
| FR-071 | Label generation form | FA Integration | `reporting/shipping_labels.php` | — |
| FR-072 | Dynamic form updates | FA Integration | `reporting/shipping_labels.php` | — |
| FR-073 | Non-numeric registration | FA Integration | `hooks.php` | — |

---

## Non-Functional Requirements → Verification

| NFR ID | Description | Verification Method |
|--------|-------------|-------------------|
| NFR-001 | PDF gen < 2s | Performance test (manual timing) |
| NFR-002 | Avery sheet < 5s | Performance test |
| NFR-010 | PHP 7.3+ | CI matrix / local test |
| NFR-030 | SOLID compliance | Code review |
| NFR-031 | PSR standards | PHPStan / PHPCS |
| NFR-032 | 100% test coverage | PHPUnit coverage report |
| NFR-033 | PHPDoc coverage | Code review / PHPStan |
| NFR-040 | New carrier = 1 class | Architecture review |
| NFR-041 | New plugin = 1 class + register | Architecture review |
| NFR-051 | No core modification | File diff / code review |

---

## Use Cases → Functional Requirements

| Use Case | Functional Requirements |
|----------|----------------------|
| UC-001: Paired Envelope | FR-010, FR-020, FR-021, FR-023, FR-032, FR-041, FR-050, FR-051, FR-060 |
| UC-002: Company Labels | FR-010, FR-023, FR-030, FR-042, FR-060 |
| UC-003: Contact Thermal | FR-010, FR-020, FR-021, FR-031, FR-043, FR-060 |
| UC-004: Contact Avery | FR-010, FR-020, FR-021, FR-031, FR-042, FR-060 |
