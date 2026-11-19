# D4 Restore drill record

| Field | Value |
|---|---|
| Date (UTC) | 2026-11-18 01:44:06 |
| Database | rgv_system (MySQL/MariaDB 10.4.32) |
| Backup command | mysqldump --single-transaction --routines --events --no-tablespaces |
| Dump file | rgv_system_20261118T014405Z.sql |
| Dump size | 195686 bytes |
| Dump SHA-256 | 9452CE202255083283E2BBD391110DBA... |
| Restore target | rgv_restore_test (separate database, not production) |
| Tables compared | 7 |
| Row-count mismatches | 0 |
| Foreign keys restored | 27 |
| Result | PASS |

Row counts compared: users, inventories, inventory_categories, bookings, borrow_requests, borrow_items, roles.