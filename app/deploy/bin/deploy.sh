#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${EUID}" -eq 0 ]]; then
    echo "Run this script as the deployment user, not root." >&2
    exit 1
fi

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE=(
    sudo docker compose
    --env-file "${APP_DIR}/.env.production"
    --file "${APP_DIR}/compose.production.yaml"
)

cd "${APP_DIR}"

git pull --ff-only

"${COMPOSE[@]}" build --pull

was_running=false

if "${COMPOSE[@]}" ps --status running --services \
    | grep -qx app; then
    was_running=true
    "${COMPOSE[@]}" exec -T app \
        php artisan down --retry=60
fi

restore_application() {
    if [[ "${was_running}" == "true" ]]; then
        "${COMPOSE[@]}" exec -T app \
            php artisan up >/dev/null 2>&1 || true
    fi
}

trap restore_application EXIT

"${COMPOSE[@]}" up \
    --detach \
    --remove-orphans

"${COMPOSE[@]}" exec -T app php artisan up

trap - EXIT

"${COMPOSE[@]}" ps
curl --fail --silent --show-error \
    http://127.0.0.1:8081/up

echo
echo "Mangie deployment completed successfully."
