# Book Performance And Scale Notes

## Hardware Used

Record benchmark output from the target machine with:

```bash
php artisan books:benchmark --iterations=10 --json
```

Benchmark JSON is written to `storage/app/private/benchmarks`.

## Setup

Optional packages requested for local debugging:

```bash
composer require --dev barryvdh/laravel-debugbar beyondcode/laravel-query-detector
composer require laravel/scout
```

The install attempt in this workspace timed out before dependencies finished downloading, so the code avoids hard dependencies on those packages. Scout-compatible config and `Book::toSearchableArray()` are present.

## Seeding

Seed one million books:

```bash
MASS_BOOK_SEED=true MASS_BOOK_SEED_COUNT=1000000 MASS_BOOK_SEED_CHUNK=5000 php artisan db:seed --class=MassBookSeeder
```

Verify the count:

```bash
php artisan tinker
>>> App\Models\Book::count();
```

The seeder uses chunked `DB::table('books')->insert()` batches instead of Eloquent `create()`, disables query logging, and reports memory after each chunk.

## Performance Targets

- ISBN lookup: `<50ms`
- Catalog cursor page: `<100ms`
- Category filter: `<150ms`
- Full-text search: `<300ms`
- Repeated cached query: `<10ms`

SQLite uses `LIKE` fallback search. MySQL gets a real `FULLTEXT` index through the migration.

## Scalability Features

- Covering/composite indexes on active catalog, category filtering, sales/rating, ISBN.
- Cursor pagination through `BookRepository`.
- Column selection and controlled eager loading.
- Cache service with tag support where the configured cache store supports tags.
- Observer-driven cache invalidation and search indexing queue rows.
- Warm cache job for top books per category.
- Materialized reporting table: `mv_bestseller_stats`.
- Scheduled refresh: `books:refresh-bestseller-stats`.
- Read/write split config for MySQL via `DB_READ_HOSTS`, `DB_WRITE_HOST`, and `DB_STICKY`.
- Redis separation for default/cache/session/queue DBs.
- Sharding metadata table and config scaffold.

## Load Test

```bash
php artisan books:load-test --users=50 --requests=10
```

This simulates repository calls in-process. Use a real HTTP load tool such as k6, wrk, or JMeter for network-level evidence.
