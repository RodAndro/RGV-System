# Diagrams

Editable sources for the figures required by D1. Export each to PNG/SVG at 300 dpi or vector.

| File | Purpose | D1 figure |
|---|---|---|
| [architecture.mmd](architecture.mmd) | Deployed system architecture | Figure 4.1 |
| [deployment.mmd](deployment.mmd) | Docker Compose deployment view | Figure 4.2 |
| [erd.mmd](erd.mmd) | Entity-relationship diagram | Figure 4.3 |
| [login-sequence.mmd](login-sequence.mmd) | API login sequence | Figure 4.4 |
| [ci-cd.mmd](ci-cd.mmd) | Pipeline stages | Figure 7.1 |

Render with the Mermaid CLI:

```bash
npx @mermaid-js/mermaid-cli -i diagrams/architecture.mmd -o diagrams/architecture.svg
```

draw.io equivalents may be committed alongside as `.drawio` files. `TODO(team)` — export and
commit the rendered images before producing the PDF.
