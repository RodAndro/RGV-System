# D4 — Research Paper

The current paper is [Research Paper.pdf](../Research%20Paper.pdf).

## Required structure (IEEE conference template, 6–8 pages)

- Title
- Abstract (150–250 words, must contain at least one measured result) — `TODO(team)`
- Index Terms
- I. Introduction — problem, objectives, contributions
- II. Related Work
- III. System Design — architecture, ERD, API
- IV. Implementation and Deployment — stack, CI/CD, hosting
- V. Evaluation — test results, SUS and ISO/IEC 25010 results, performance
- VI. Discussion — limitations, threats to validity
- VII. Conclusion and Future Work
- Acknowledgment — **must declare AI tool use**
- References — at least 12, IEEE style

## Editable source

`TODO(team)` — commit the editable source (`.docx` or `.tex`) beside the PDF.

## Figures to reuse

The diagrams in [../diagrams](../diagrams) are the paper's Figures 1–3 (architecture, ERD, pipeline).

## Measured results available from this repository

These are real, measured locally and may be cited once the team confirms the final numbers:

- Backend automated tests: **201 tests, 951 assertions** passing.
- Spa client tests: **8 tests** passing (Vitest); ESLint and production build clean.
- Flutter: existing suite passing.

Results that must still be measured on the deployed system: coverage percentage, AI evaluation
(correctness and latency), SUS mean, ISO/IEC 25010 means, and deployment lead time.
