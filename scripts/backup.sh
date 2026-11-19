#!/usr/bin/env bash
#
# Back up the MySQL database for the RGV system.
#
# Requirement D4: a documented backup and restore procedure, tested at least
# once. This script is the backup half; see restore.sh for the restore half.
#
# Usage:
#   ./scripts/backup.sh                # containerised stack (docker compose)
#   ./scripts/backup.sh --native       # a local MySQL server instead
#
# Environment (read from the repository .env when present):
#   DB_DATABASE, DB_USERNAME, DB_PASSWORD
#
# The dump is a consistent InnoDB snapshot written outside the database
# volume, so losing the container does not lose the backup.

set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-$HOME/backups}"
MODE="${1:-compose}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"

# Load .env if present so the caller does not have to export everything.
if [[ -f .env ]]; then
    set -a
    # shellcheck disable=SC1091
    source .env
    set +a
fi

DB_DATABASE="${DB_DATABASE:-rgv}"
DB_USERNAME="${DB_USERNAME:-rgv}"
DB_PASSWORD="${DB_PASSWORD:-rgv_local_password}"

mkdir -p "$BACKUP_DIR"
OUTPUT="$BACKUP_DIR/${DB_DATABASE}_${TIMESTAMP}.sql"

echo "Backing up database '${DB_DATABASE}' to ${OUTPUT}"

if [[ "$MODE" == "--native" ]]; then
    MYSQL_PWD="$DB_PASSWORD" mysqldump \
        --host="${DB_HOST:-127.0.0.1}" \
        --port="${DB_PORT:-3306}" \
        --user="$DB_USERNAME" \
        --single-transaction \
        --routines \
        --events \
        --no-tablespaces \
        "$DB_DATABASE" > "$OUTPUT"
else
    docker compose exec -T db mysqldump \
        --single-transaction \
        --routines \
        --events \
        --no-tablespaces \
        -u "$DB_USERNAME" \
        -p"$DB_PASSWORD" \
        "$DB_DATABASE" > "$OUTPUT"
fi

# A dump smaller than a few hundred bytes is almost certainly an error page.
SIZE=$(wc -c < "$OUTPUT")
if [[ "$SIZE" -lt 200 ]]; then
    echo "ERROR: dump is only ${SIZE} bytes; treating as failed." >&2
    exit 1
fi

echo "Backup complete: ${SIZE} bytes"
echo "$OUTPUT"
