# Test Plan

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Test Strategy

### 1.1 Approach
- **TDD**: All domain classes developed using Red-Green-Refactor cycle
- **Unit tests**: Isolated tests with mocked dependencies for all domain logic
- **Integration tests**: End-to-end label generation with mocked FA session
- **Manual tests**: Physical print verification on actual media

### 1.2 Tools
| Tool | Purpose |
|------|---------|
| PHPUnit | Unit and integration testing |
| Mockery/PHPUnit Mocks | Dependency mocking |
| Coverage Reporter | HTML + text coverage reports |

---

## 2. Unit Test Suites

### TS-001: Address Value Object (`AddressTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-001 | FR-001 | Create Address with all fields |
| TC-002 | FR-001 | Create Address with optional line2 empty |
| TC-003 | FR-002 | Reject Address with missing required fields |
| TC-004 | FR-003 | Accept valid Canadian postal code |
| TC-005 | FR-003 | Reject invalid postal code format |
| TC-006 | FR-001 | Address immutability (no setters) |
| TC-007 | FR-001 | toArray/fromArray round-trip |

### TS-002: Canada Post Formatter (`CanadaPostFormatterTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-010 | FR-010 | Output is all uppercase |
| TC-011 | FR-010 | No punctuation in output |
| TC-012 | FR-010 | Municipality, province, postal code on same line |
| TC-013 | FR-010 | One space between municipality and province |
| TC-014 | FR-010 | Two spaces between province and postal code |
| TC-015 | FR-010 | Province uses 2-letter abbreviation |
| TC-016 | FR-010 | Max 40 chars per line |
| TC-017 | FR-010 | 3–6 lines output |
| TC-018 | FR-010 | No `#` symbol in output |
| TC-019 | FR-010 | No "CANADA" on domestic address |
| TC-020 | FR-011 | Country name appended for international |

### TS-003: Label Medium (`LabelMediumTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-030 | FR-041 | All envelope sizes have valid positive dimensions |
| TC-031 | FR-041 | Medium width ≤ Letter/A4 width |
| TC-032 | FR-041 | Medium registry returns known sizes |

### TS-004: Label Layout (`LabelLayoutTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-040 | FR-050 | Return address position in top-left quadrant |
| TC-041 | FR-051 | Destination position in center/lower area |
| TC-042 | FR-052 | Font size produces 2–5mm characters |
| TC-043 | FR-053 | All address blocks fit within medium boundaries |
| TC-044 | FR-053 | Layout adapts for each medium size |

### TS-005: Avery Template (`AveryTemplateTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-050 | FR-042 | 5160: 30 labels fit on Letter sheet |
| TC-051 | FR-042 | 5163: 10 labels fit on Letter sheet |
| TC-052 | FR-042 | L7160: 21 labels fit on A4 sheet |
| TC-053 | FR-042 | L7163: 14 labels fit on A4 sheet |
| TC-054 | FR-042 | No label overlaps or exceeds sheet edges |

### TS-006: Thermal Format (`ThermalFormatTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-060 | FR-043 | 4×6 dimensions correct |
| TC-061 | FR-043 | 2¼×1¼ dimensions correct |
| TC-062 | FR-043 | Dymo dimensions correct |

### TS-007: Contact Types (`CustomerContactTest.php`, `SupplierContactTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-070 | FR-020 | Customer contact returns valid Address |
| TC-071 | FR-025 | Customer branches listed correctly |
| TC-072 | FR-021 | Supplier contact returns valid Address |
| TC-073 | FR-022 | Employee contact throws not-supported |
| TC-074 | FR-023 | Company address retrieved from FA settings |

---

## 3. Integration Test Suite

### TS-100: Label Generation (`LabelGenerationTest.php`)
| Test Case | Requirement | Description |
|-----------|------------|-------------|
| TC-100 | FR-060 | Envelope plugin generates valid PDF (has `%PDF-` header) |
| TC-101 | FR-060 | Avery plugin generates valid PDF |
| TC-102 | FR-060 | Thermal plugin generates valid PDF |
| TC-103 | FR-030 | Company-only label generated successfully |
| TC-104 | FR-031 | Contact-only label generated successfully |
| TC-105 | FR-032 | Paired label has both address blocks |

---

## 4. Manual Test Cases

### TM-001: Envelope Print Verification
1. Generate #10 envelope paired label
2. Print on plain paper
3. Overlay on physical #10 envelope
4. Verify return address in top-left, destination in center

### TM-002: Avery Sheet Verification
1. Generate Avery 5160 company labels
2. Print on Avery 5160 label stock
3. Verify labels align with die-cut positions

### TM-003: Thermal Printer Verification
1. Generate 4×6 thermal label
2. Print on thermal printer
3. Verify address centered and readable

---

## 5. Coverage Requirements

| Component | Target Coverage |
|-----------|----------------|
| Address domain | 100% |
| Formatters | 100% |
| Label layout | 100% |
| Templates/Formats | 100% |
| Contact types | 100% (with mocks) |
| Renderer | 80% (PDF internals hard to unit test) |
| Plugins | 90% |
