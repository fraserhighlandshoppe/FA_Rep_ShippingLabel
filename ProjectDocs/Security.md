# Security Document

## Document Control

| Field | Value |
|-------|-------|
| Project | FA_Rep_ShippingLabel |
| Version | 1.0 |
| Status | Draft |
| Date | 2026-02-25 |

---

## 1. Access Control

### SEC-001: FA Authentication
The module SHALL rely on FrontAccounting's built-in session and authentication system. No custom authentication is implemented.

### SEC-002: Report Access Permissions
Access to the Shipping Labels report SHALL be controlled by FA's role-based access control. Only users with the appropriate reporting permissions can access the module.

### SEC-003: No Direct Script Access
All PHP files SHALL include a guard preventing direct URL access, requiring FA session initialization.

---

## 2. Input Validation

### SEC-010: Contact ID Validation
All contact IDs received from the UI form SHALL be validated as positive integers before database queries.

### SEC-011: Branch ID Validation
Branch/destination IDs SHALL be validated as positive integers or null.

### SEC-012: Enum Validation
Label Type, Output Plugin, and Output Format selections SHALL be validated against known allowed values (whitelist approach).

### SEC-013: No User-Supplied Text in Labels
Address data is sourced entirely from the FA database — no free-text user input is rendered on labels, eliminating injection risk in PDF output.

---

## 3. SQL Injection Prevention

### SEC-020: Parameterized Queries
All database queries SHALL use FA's database abstraction layer with parameterized queries or proper escaping via `db_escape()`.

### SEC-021: No Raw SQL Concatenation
No query SHALL concatenate user-supplied values directly into SQL strings.

---

## 4. XSS Protection

### SEC-030: HTML Output Encoding
All values displayed in the HTML form SHALL use FA's output encoding functions to prevent cross-site scripting.

### SEC-031: PDF Output (Non-HTML)
PDF output is binary, not HTML. Address text is rendered directly into PDF drawing commands, which are inherently not susceptible to XSS.

---

## 5. CSRF Protection

### SEC-040: Form Token
The label generation form SHALL use FA's built-in form submission handling, which includes CSRF protection via hidden token fields.

---

## 6. Data Privacy

### SEC-050: No Data Persistence
The module does NOT store any additional data. It reads existing FA data, formats it, and generates a transient PDF. No label history or address cache is maintained.

### SEC-051: Address Data Sensitivity
Customer and supplier addresses are already stored in FA. This module introduces no new attack surface for address data — it only provides a read-only view formatted for labels.

---

## 7. Audit

### SEC-060: Report Generation Logging
Label generation events SHOULD be logged via FA's event/logging system (if available) for audit trail purposes, recording: user, contact type, contact ID, output format, timestamp.
