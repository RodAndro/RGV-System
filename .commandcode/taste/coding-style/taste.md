# Coding Style Preferences

- Prefers never hardcoding database passwords or other secrets in source code or prompts; uses environment variables (e.g., `DATABASE_URL`, `DB_PASSWORD`) instead. Confidence: 0.95
- Prefers not fabricating or guessing missing data values; uses `NULL`/defaults/empty fields and flags ambiguous records for review rather than assuming. Confidence: 0.9
- Prefers the database as the single source of truth over hardcoded arrays or demo JSON. Confidence: 0.85
