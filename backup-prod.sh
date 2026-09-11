#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"
OUTPUT_DIR="${BACKUP_OUTPUT_DIR:-backups}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_NAME="visitorportal-${TIMESTAMP}"
WORK_DIR=""

usage() {
    cat <<'EOF'
Usage: ./backup-prod.sh [--output DIR]

Creates and verifies a production backup containing:
  - a transaction-consistent MariaDB logical dump
  - the application storage volume
  - SHA-256 checksums and backup metadata

The production Compose stack must be running. Configure it through the same
environment variables or .env file used for docker-compose.prod.yml.
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --output)
            [[ $# -ge 2 ]] || { printf 'Missing value for --output.\n' >&2; exit 2; }
            OUTPUT_DIR="$2"
            shift 2
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        *)
            printf 'Unknown option: %s\n' "$1" >&2
            usage >&2
            exit 2
            ;;
    esac
done

command -v docker >/dev/null 2>&1 || { printf 'docker is required.\n' >&2; exit 1; }
command -v gzip >/dev/null 2>&1 || { printf 'gzip is required.\n' >&2; exit 1; }
command -v sha256sum >/dev/null 2>&1 || { printf 'sha256sum is required.\n' >&2; exit 1; }
command -v tar >/dev/null 2>&1 || { printf 'tar is required.\n' >&2; exit 1; }

mkdir -p "$OUTPUT_DIR"
WORK_DIR="$(mktemp -d "${OUTPUT_DIR%/}/.${BACKUP_NAME}.XXXXXX")"
trap '[[ -n "$WORK_DIR" ]] && rm -rf "$WORK_DIR"' EXIT

printf 'Creating database dump...\n'
docker compose -f "$COMPOSE_FILE" exec -T db sh -eu -c \
    'MARIADB_PWD="$MARIADB_PASSWORD" mariadb-dump --host=127.0.0.1 --user="$MARIADB_USER" --single-transaction --quick --routines --events --triggers --hex-blob "$MARIADB_DATABASE"' \
    | gzip -9 > "$WORK_DIR/database.sql.gz"

printf 'Archiving application storage...\n'
docker compose -f "$COMPOSE_FILE" exec -T app \
    tar -C /var/www/html/backend/storage -czf - . > "$WORK_DIR/storage.tar.gz"

gzip -t "$WORK_DIR/database.sql.gz"
tar -tzf "$WORK_DIR/storage.tar.gz" >/dev/null

cat > "$WORK_DIR/metadata.txt" <<EOF
created_at=${TIMESTAMP}
format_version=1
database_format=mariadb-sql-gzip
storage_format=tar-gzip
EOF

(
    cd "$WORK_DIR"
    sha256sum database.sql.gz storage.tar.gz metadata.txt > SHA256SUMS
)

ARCHIVE_PATH="${OUTPUT_DIR%/}/${BACKUP_NAME}.tar.gz"
tar -C "$WORK_DIR" -czf "$ARCHIVE_PATH" .
tar -tzf "$ARCHIVE_PATH" >/dev/null

VERIFY_DIR="$(mktemp -d "${OUTPUT_DIR%/}/.${BACKUP_NAME}.verify.XXXXXX")"
tar -C "$VERIFY_DIR" -xzf "$ARCHIVE_PATH"
(
    cd "$VERIFY_DIR"
    sha256sum --check SHA256SUMS >/dev/null
)
rm -rf "$VERIFY_DIR"

printf 'Backup created and verified: %s\n' "$ARCHIVE_PATH"