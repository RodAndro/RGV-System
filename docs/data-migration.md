# SQLite to Supabase data migration

The SQLite-to-PostgreSQL command is an explicit merge tool. It defaults to a dry-run and never resets the target. The command has not been run against any database as part of this repository change.

## Before migration

1. Confirm the source SQLite file is the intended, quiesced business database. Stop source writes while producing the final snapshot, and put the target app in a planned maintenance/read-only window during the merge so unique-key checks and PostgreSQL sequence reconciliation cannot race with live writes.
2. Create and verify protected backups of both source and target. Keep the backup outside this repository, restrict access, and retain it until reconciliation is accepted.
3. Configure Laravel's `DB_*` variables to the intended Supabase PostgreSQL target. Check that the target is the expected project in the Supabase Dashboard. This operation is never automated by deployment.
4. Apply reviewed Laravel migrations to the target, including `data_migration_mappings`. Do not use `migrate:fresh`, `db:wipe`, or a restore-over-existing operation.

## Dry-run and explicit merge

From the Laravel project root:

```powershell
php artisan rvg:migrate-sqlite-to-supabase --sqlite=database/database.sqlite
```

The dry-run checks source foreign keys, target connectivity and schema, prints source counts by application table, and performs no writes. Resolve every missing table, column, or orphan before continuing. The command intentionally excludes migrations bookkeeping, cache, sessions, password reset tokens, and queue runtime tables; those are ephemeral/runtime state and must not be carried into the new deployment.

After reviewing the plan and confirming protected backups, explicitly request a merge:

```powershell
php artisan rvg:migrate-sqlite-to-supabase --sqlite=database/database.sqlite --execute --backup-confirmed
```

Interactive mode asks once more before writing. In automation, `--yes` skips that prompt and should be used only in a reviewed, operator-controlled job. The target must be configured by environment variables; no password or key is accepted as a CLI argument.

## Merge and reconciliation behavior

- Tables are processed in dependency order, including `inventory_categories` before `inventories`, and users/roles before dependent bookings, borrow requests, items, notifications, reservations, evidence, tokens, reports, and audit records.
- Foreign keys are remapped through the target-side `data_migration_mappings` table. Stable natural keys merge matching target rows; new rows are inserted. Existing business rows are not updated or deleted.
- A target primary-key conflict without a safe natural key stops the operation instead of guessing whether a duplicate exists. Each row and its source-to-target key mapping are committed in the same transaction.
- The command reports per-table row counts and performs a final source-row to target-key mapping reconciliation. Review the final count table and run a separate application-level spot check for inventory/category assignment, request histories, reserved quantities, and return history before cutting over writers.
- Failures stop the run. The command prints only its own schema/data validation messages; raw driver exceptions and database values are suppressed to keep credentials and record contents out of command output. Fix the reported schema or data condition, then rerun; persisted mappings make completed rows repeatable.

The mapping table is additive migration metadata and is retained for future reconciliation. It does not create a second business-data store. The active website and mobile clients continue to read and write only through Laravel against the canonical Supabase PostgreSQL database.
