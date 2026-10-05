#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this backup script as root (normally from root's cron)." >&2
    exit 1
fi

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-/srv/backups/mangie}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"

COMPOSE=(
    docker compose
    --env-file "${APP_DIR}/.env.production"
    --file "${APP_DIR}/compose.production.yaml"
)

mkdir -p "${BACKUP_DIR}"

docker exec shared-mysql sh -c \
    'exec mysqldump --single-transaction --quick --routines --triggers -uroot -p"$MYSQL_ROOT_PASSWORD" mangie' \
    | gzip -9 > "${BACKUP_DIR}/database-${TIMESTAMP}.sql.gz"

"${COMPOSE[@]}" exec -T app \
    tar -C /var/www/html/storage/app/public -czf - . \
    > "${BACKUP_DIR}/uploads-${TIMESTAMP}.tar.gz"

find "${BACKUP_DIR}" \
    -type f \
    \( -name 'database-*.sql.gz' -o -name 'uploads-*.tar.gz' \) \
    -mtime "+${RETENTION_DAYS}" \
    -delete

echo "Mangie backup completed at ${TIMESTAMP}."
