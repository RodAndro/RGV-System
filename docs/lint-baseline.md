# Lint baseline

## Backend (Laravel Pint)

`vendor/bin/pint --test` over the whole backend currently reports style
findings in **115 pre-existing files**:

| Directory | Files failing |
|---|---|
| `app/` | 78 |
| `database/` | 21 |
| `tests/` | 8 |
| `bootstrap/` | 3 |
| `config/` | 2 |
| `routes/` | 3 |

These findings pre-date the versioned-API work and are mostly
`class_definition` / `braces_position` on anonymous migration classes,
`ordered_imports`, `line_ending` and `not_operator_with_successor_space`.

### What CI enforces

CI runs Pint against the modules introduced or touched by the API work,
which are fully clean (25 files):

```
app/Http/Controllers/Api/V1
app/Http/Requests/Api
app/Http/Resources
app/Http/Middleware/EnsureApiRole.php
app/Support
bootstrap/app.php
routes/api.php
tests/Feature/Api
tests/Feature/ApiHealthTest.php
tests/Feature/ApiHealthDegradedTest.php
```

### Closing the gap

Reformatting 115 unrelated files in the same change would bury the
functional diff and risk unrelated regressions. To adopt a project-wide
gate:

1. Run `vendor/bin/pint` on a dedicated `chore/pint-baseline` branch.
2. Confirm `php artisan test` still passes (201 tests at the time of
   writing).
3. Open that as a formatting-only PR, then widen the CI step to
   `vendor/bin/pint --test app bootstrap config database routes tests`.
