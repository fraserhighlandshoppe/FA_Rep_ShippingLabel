# Business Requirements Document

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Business Objectives

### 1.1 Purpose
Provide FrontAccounting users with the ability to generate properly formatted, standards-compliant mailing and shipping labels directly from their accounting system, eliminating manual address transcription and ensuring postal compliance.

### 1.2 Business Goals
- **BR-001**: Reduce time spent manually addressing envelopes and packages by 90%
- **BR-002**: Ensure 100% compliance with Canada Post T601 addressing standards
- **BR-003**: Support multiple output media (envelopes, label sheets, thermal labels)
- **BR-004**: Leverage existing Customer and Supplier address data in FrontAccounting
- **BR-005**: Provide extensible architecture for future carrier standards and output formats

### 1.3 Success Metrics
| Metric | Target |
|--------|--------|
| Address formatting errors | 0% (automated formatting) |
| Supported output formats at launch | 3 plugins (Envelope, Avery, Thermal) |
| Contact types supported | Customer, Supplier (Employee stubbed) |
| Time from selection to printed label | < 30 seconds |

---

## 2. Scope

### 2.1 In Scope
- Generate mailing labels for Customers and Suppliers from FA data
- Company return address labels (standalone)
- Paired envelope labels (return + destination)
- Canada Post T601 compliant address formatting
- Multiple output formats:
  - Direct-print on envelopes (#10, DL, C5, C4, 6×9, 9×12)
  - Avery label sheets (5160, 5163, L7160, L7163)
  - Thermal printer labels (4×6, 2¼×1¼, Dymo)
- PDF output for print or save
- FA module integration (reports menu, hooks system)

### 2.2 Out of Scope (Phase 1)
- Employee contact type (interface stubbed, no data source)
- Barcode/QR code on labels (future TCPDF migration)
- Batch label generation from transaction lists
- Direct thermal printer communication (prints via PDF)
- Non-Canada-Post carrier formatting (architecture supports it, not implemented Phase 1)

### 2.3 Future Extensions
- UPS/Purolator/DHL/FedEx formatting strategies
- European address formatting (DIN 5008, Royal Mail)
- Employee address support
- Bulk label printing from invoice/PO batches
- Barcode and tracking number integration

---

## 3. Stakeholders

| Stakeholder | Role | Interest |
|-------------|------|----------|
| FA Administrator | Installs/configures module | Easy installation, minimal configuration |
| Accounts Receivable | Prints customer mailing labels | Quick access to customer addresses |
| Accounts Payable | Prints supplier mailing labels | Quick access to supplier addresses |
| Office Admin | Prints company return address labels | Batch label printing on Avery sheets |
| Shipping Department | Prints shipping labels for parcels | Thermal printer support, correct sizing |

---

## 4. Business Rules

- **RULE-001**: All Canadian addresses MUST follow Canada Post T601 formatting
- **RULE-002**: Return address MUST be smaller font than destination address
- **RULE-003**: Return address placement: top-left corner of envelope
- **RULE-004**: Destination address placement: center/lower-center of envelope
- **RULE-005**: Province/territory MUST use official two-letter abbreviations
- **RULE-006**: Postal code format: uppercase `A1A 1A1` (no hyphens)
- **RULE-007**: Municipality, province, and postal code on same line with standard spacing (1sp, 2sp)
- **RULE-008**: Do not include "CANADA" on domestic addresses
- **RULE-009**: International addresses include country name as last line in full capitals

---

## 5. Constraints

- Must run on PHP 7.3+ (FA minimum requirement)
- Must use FA's existing PDF generation (`FrontReport` class) for Phase 1
- Must not modify FA core files — all code in `modules/` directory
- Must work with FA's session/authentication system
- Maximum print medium is Letter/A4 paper size
