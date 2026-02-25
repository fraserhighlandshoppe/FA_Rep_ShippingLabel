# Non-Functional Requirements

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Performance

### NFR-001: PDF Generation Time
The system SHALL generate a single label PDF in under 2 seconds on standard hardware.

### NFR-002: Avery Sheet Generation Time
The system SHALL generate a full Avery sheet PDF (up to 30 labels) in under 5 seconds.

### NFR-003: Memory Usage
PDF generation SHALL not exceed 32MB of additional memory beyond FA's base allocation.

---

## 2. Compatibility

### NFR-010: PHP Version
The system SHALL be compatible with PHP 7.3 and above.

### NFR-011: FrontAccounting Version
The system SHALL be compatible with FrontAccounting 2.4.x.

### NFR-012: Browser Compatibility
The generated PDF SHALL be viewable in all modern browsers (Chrome, Firefox, Edge, Safari).

### NFR-013: Printer Compatibility
The generated PDF SHALL print correctly on standard inkjet/laser printers and thermal label printers that support PDF input.

---

## 3. Usability

### NFR-020: Minimal Clicks
The user SHALL be able to generate a label in 5 or fewer clicks from the Reports menu.

### NFR-021: FA UI Consistency
The label generation form SHALL use FA's native UI components and styling for a consistent user experience.

### NFR-022: Error Messages
All validation errors SHALL be displayed using FA's standard error display mechanism with clear, actionable messages.

---

## 4. Maintainability

### NFR-030: SOLID Compliance
All classes SHALL follow SOLID principles as specified in AGENTS-TECH.md.

### NFR-031: PSR Standards
Code SHALL comply with PSR-1, PSR-2, PSR-4, and PSR-12.

### NFR-032: Test Coverage
Unit test coverage SHALL target 100% for all domain classes (Address, Formatters, Layout, Templates).

### NFR-033: PHPDoc Coverage
All classes, methods, and properties SHALL have complete PHPDoc blocks.

---

## 5. Extensibility

### NFR-040: New Carrier Formats
Adding a new carrier formatting strategy SHALL require only implementing `AddressFormatterInterface` — no modifications to existing code.

### NFR-041: New Output Plugins
Adding a new output plugin SHALL require only implementing `OutputPluginInterface` and registering with the plugin registry.

### NFR-042: New Label Sizes
Adding a new envelope/label size SHALL require only adding an entry to the appropriate medium/template registry.

---

## 6. Reliability

### NFR-050: Graceful Degradation
If a contact has incomplete address data, the system SHALL display a clear error rather than generating a malformed label.

### NFR-051: No Core Modification
The module SHALL NOT modify any FrontAccounting core files. All code SHALL reside in the `modules/` directory.

---

## 7. Portability

### NFR-060: Database Independence
The module SHALL use FA's existing database abstraction layer and not require additional database tables in Phase 1.

### NFR-061: Environment Independence
The module SHALL work in any environment supported by FrontAccounting (Apache/Nginx, MySQL/MariaDB).
