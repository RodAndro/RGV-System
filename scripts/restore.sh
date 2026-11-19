#!/usr/bin/env bash
#
# Restore a MySQL backup into a database.
#
# Requirement D4: the restore procedure must be documented AND tested at least
# once. By default this refuses to touch the live database: you must name a
# separate target, verify it, and only then promote it.
#
# Usage:
#   ./scripts/restore.sh <dump.sql> --database rgv_restore_test
#   ./scripts/restore.sh <dump.sql> --database rgv --force-production
#
# Verification prints row counts for the key tables so the restore can be
# recorded in the Deployment and Operations Manual.

set -euo pipefail

DUMP_FILE="${1:-}"
MODE="compose"
DATABASE=""
FORCE_PRODUCTION="false"

shift || true

while [[ $# -gt 0 ]]; do
    case "$1" in
        --database) DATABASE="$2"; shift 2 ;;
        --native) MODE="--native"; shift ;;
        --force-production) FORCE_PRODUCTION="true"; shift ;;
        *) echo "Unknown option: $1" >&2; exit 1 ;;
    esac
done

if [[ -z "$DUMP_FILE" || ! -f "$DUMP_FILE" ]]; then
    echo "Usage: $0 <dump.sql> --database <name> [--native] [--force-production]" >&2
    exit 1
fi

if [[ -z "$DATABASE" ]]; then
    echo "ERROR: --database is required." >&2
    exit 1
fi

if [[ -f .env ]]; then
    set -a
    # shellcheck disable=SC1091
    source .env
    set +a
fi

LIVE_DB="${DB_DATABASE:-rgv}"

if [[ "$DATABASE" == "$LIVE_DB" && "$FORCE_PRODUCTION" != "true" ]]; then
    echo "Refusing to restore over the live database '${LIVE_DB}'." >&2
    echo "Restore into a separate database first, verify it, then promote it." >&2
    echo "Pass --force-production only if you are certain." >&2
    exit 1
fi

DB_USERNAME="${DB_USERNAME:-rgv}"
DB_PASSWORD="${DB_PASSWORD:-rgv_local_password}"

run_sql() {
    if [[ "$MODE" == "--native" ]]; then
        MYSQL_PWD="$DB_PASSWORD" mysql \
            --host="${DB_HOST:-127.0.0.1}" \
            --port="${DB_PORT:-3306}" \
            --user="$DB_USERNAME" \
            "$@"
    else
        docker compose exec -T db mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$@"
    fi
}

echo "Creating database '${DATABASE}' if needed"
run_sql -e "CREATE DATABASE IF NOT EXISTS \`${DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Restoring ${DUMP_FILE} into ${DATABASE}"
if [[ "$MODE" == "--native" ]]; then
    MYSQL_PWD="$DB_PASSWORD" mysql \
        --host="${DB_HOST:-127.0.0.1}" \
        --port="${DB_PORT:-3306}" \
        --user="$DB_USERNAME" \
        --database="$DATABASE" < "$DUMP_FILE"
else
    docker compose exec -T db sh -c "mysql -u '$DB_USERNAME' -p'$DB_PASSWORD' '$DATABASE'" < "$DUMP_FILE"
fi

echo
echo "Verification (record these numbers in the manual):"
run_sql --database="$DATABASE" -e "
    SELECT 'users' AS table_name, COUNT(*) AS rows_restored FROM users
    UNION ALL SELECT 'inventories', COUNT(*) FROM inventories
    UNION ALL SELECT 'inventory_categories', COUNT(*) FROM inventory_categories
    UNION ALL SELECT 'bookings', COUNT(*) FROM bookings
    UNION ALL SELECT 'borrow_requests', COUNT(*) FROM borrow_requests
    UNION ALL SELECT 'borrow_items', COUNT(*) FROM borrow_items
    UNION ALL SELECT 'roles', COUNT(*) FROM roles;
"

echo
echo "Restore complete. Compare the counts above with the source database"
echo "before promoting this database to production."
