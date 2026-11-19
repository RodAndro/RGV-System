# Defense presentation

Outline for the 15-minute presentation plus demonstration (ITSD 85 §11).

| Segment | Time | Slides |
|---|---|---|
| Introduction — problem, users, objectives | 2 min | 1–3 |
| Architecture and design — architecture, ERD, API, security | 2 min | 4–6 |
| Live demonstration on the public URL | 5 min | — |
| Technology integration + DevOps demonstration | 5 min | 7–8 |
| Evaluation results | 1 min | 9 |

## Live demonstration checklist

- [ ] Sign in as each role (public, employee, administrator).
- [ ] Complete the main workflow end to end: borrow request → approval → return.
- [ ] Show an authorization check: employee attempting an administrator action is refused.
- [ ] Open `/api/docs` and exercise one endpoint from Swagger UI.
- [ ] Show the AI assistant answering from report data, and its fallback when the model is offline.
- [ ] Merge a small change; show the pipeline testing and deploying it.
- [ ] Show `/api/health` reporting the new commit ID.
- [ ] Present coverage, SUS and ISO/IEC 25010 results.

## Materials to bring

- One printed scoresheet (Appendix A) per panel member, header and names pre-filled.
- Recorded backup video in case the venue network fails.
- Copy of D1, D2 and D4 PDFs.

`TODO(team)` — export the final slide deck to PDF and commit it here.
