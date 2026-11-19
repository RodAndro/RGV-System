# D1 — System Documentation (source)

**Status:** outline seeded. Chapters requiring measured data are marked `TODO(team)`.

This file is the editable source for the System Documentation required by ITSD 85 §9.2.
Convert to PDF with the school format (A4, 1.5 in left margin, Times New Roman 12 pt, 1.5 line
spacing) before submission.

---

## Front matter

- Title page — `TODO(team)`
- Approval sheet (instructor signature) — `TODO(team)`
- Abstract (200–300 words) — `TODO(team)`
- Acknowledgment (must declare AI tool use) — `TODO(team)`
- Table of contents, list of figures, list of tables — generate after the PDF is complete

## Chapter 1 — Introduction

**1.1 Background.** RGV Multi-Tech Services handles service bookings, an equipment inventory, and
employee equipment borrowing. These were tracked in separate manual records, which made stock
availability and borrowing history hard to reconcile.

**1.2 Problem statement.** `TODO(team)`

**1.3 Objectives.** `TODO(team)` — one general and 3–5 specific, measurable objectives.

**1.4 Scope and limitations.** The system covers public booking and inventory QR lookup, employee
borrowing, administrator inventory/user/report management, and an AI report assistant. Excluded:
payroll, accounting, supplier procurement, and payment processing.

**1.5 Significance.** `TODO(team)`

**1.6 Definition of terms.** `TODO(team)`

## Chapter 2 — Review of Related Literature and Systems

`TODO(team)` — at least three existing systems compared against this one, plus a comparison table
showing the gap (integration, roles, API, deployment).

## Chapter 3 — Requirements Specification

**3.1 Users and roles.** Public visitor, employee, administrator. See
[../System Features and Role Permissions.md](../System%20Features%20and%20Role%20Permissions.md).

**3.2 Functional requirements.** User stories with acceptance criteria — `TODO(team)`.

**3.3 Non-functional requirements.** Security, performance, usability, availability, privacy.

**3.4 Requirements traceability matrix.** Requirement → API endpoint → screen → test case.
Endpoint inventory is in `Backend/routes/api.php`; tests are under
`Backend/tests/Feature/Api/V1`. Completing every row is `TODO(team)`.

**Figures/tables:** use case diagram, roles and permissions matrix, traceability matrix.

## Chapter 4 — System Design

**4.1 Architecture.** See [../architecture.md](../architecture.md) and the diagram in `README.md`.

**4.2 Database design.** Entity-relationship diagram, data dictionary — see
[../Complete Database Schema.md](../Complete%20Database%20Schema.md).

Main entities: `users`, `inventory_categories`, `inventories`, `borrow_requests`, `borrow_items`,
`borrow_return_evidences`, `bookings`, `notifications`, `audit_logs`, `chatbot_interactions`.

**4.3 API design.** Base path `/api/v1`; conventions and full schemas in
[../api/openapi.yaml](../api/openapi.yaml).

**4.4 UI design.** Wireframes versus final screens — `TODO(team)`.

**4.5 Security design.** Sanctum bearer tokens, hashed passwords (bcrypt), Spatie role enforcement
per route, server-side validation via FormRequests, CORS restricted to `FRONTEND_URL`, login rate
limiting, security headers, audit logging.

**4.6 Technology integration design.** Client → `POST /api/v1` → Laravel → Ollama. Model output is
recorded in `chatbot_interactions` (question hash, output, model, user, timestamp) and a
deterministic fallback answers when the model is unavailable.

**Figures:** architecture diagram, deployment diagram, ERD, login sequence diagram.

## Chapter 5 — Implementation

**5.1 Tech stack and rationale.** `TODO(team)` — table with a reason per choice.

**5.2 Project structure.** See `README.md` (repository layout).

**5.3 Key modules.** `BorrowService` (stock reservation and return), `Api/V1/*` controllers,
`EnsureApiRole` middleware, `HealthProbe`.

**5.4 Coding standards.** Laravel Pint (see [../lint-baseline.md](../lint-baseline.md)), ESLint.

**5.5 Git workflow.** Feature branches, pull requests into `main`, required status checks.

**Figures:** selected short code listings.

## Chapter 6 — Testing and Evaluation

**6.1 Test plan.** `TODO(team)` — scope, levels, environments.

**6.2 Automated test results.** Backend: **201 tests, 951 assertions** passing on in-memory SQLite
(measured locally). SPA: **8 tests** passing (Vitest). Flutter: existing suite passing.

**6.3 Coverage.** The 60% line-coverage gate runs in CI with PCOV. It **cannot be measured on a
machine without PCOV or Xdebug**; the figure must be copied from the CI run — `TODO(team)`.

**6.4 User acceptance testing.** Requires at least five target users — `TODO(team)`. No responses
have been collected or simulated.

**6.5 SUS and ISO/IEC 25010 results.** `TODO(team)` — report n, mean and interpretation.

**6.6 Technology integration evaluation (T5).** `TODO(team)` — correctness and response time over
the question set in [../evaluation/chatbot-questions.json](../evaluation/chatbot-questions.json).
No results have been fabricated.

**Tables:** test cases with pass/fail, coverage summary, SUS/ISO tables.

## Chapter 7 — Deployment and Operations

See [../deployment.md](../deployment.md) and [../cicd-runbook.md](../cicd-runbook.md).

- Compose stack: `db`, `app`, `web`, `caddy`, `ollama`, and an optional `tunnel`.
- Only the proxy publishes ports; data persists in the `db-data` volume.
- CI/CD deploys on merge to `main` and verifies the commit at `/api/health`.
- Backup: `pg_dump` to a file outside the container; restore procedure documented and must be
  executed once — `TODO(team)` record date, file and rows checked.

## Chapter 8 — Summary, Conclusions and Recommendations

`TODO(team)` — objectives versus results table, conclusions, future work.

## References

`TODO(team)` — at least 15 sources in IEEE style, at least 8 from the last five years.

## Appendices

A. User manual (D2) · B. API reference export · C. Test cases · D. UAT instrument and anonymized
results · E. Consent form template · F. Gantt chart / work log · G. Repository, release and live URL
· H. Team member profiles
