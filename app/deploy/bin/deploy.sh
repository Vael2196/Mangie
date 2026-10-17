#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${EUID}" -eq 0 ]]; then
    echo "Run this script as the deployment user, not root." >&2
    exit 1
fi

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPO_DIR="$(cd "${APP_DIR}/.." && pwd)"
DEPLOY_BRANCH="${MANGIE_DEPLOY_BRANCH:-master}"
LOCK_FILE="${MANGIE_DEPLOY_LOCK_FILE:-/tmp/mangie-deploy-${UID}.lock}"

exec 9>"${LOCK_FILE}"

if ! flock --nonblock 9; then
    echo "Another Mangie deployment is already running; nothing to do."
    exit 0
fi

if docker info >/dev/null 2>&1; then
    DOCKER=(docker)
else
    DOCKER=(sudo -n docker)
fi

COMPOSE=(
    "${DOCKER[@]}" compose
    --env-file "${APP_DIR}/.env.production"
    --file "${APP_DIR}/compose.production.yaml"
)

current_branch="$(
    git -C "${REPO_DIR}" symbolic-ref --quiet --short HEAD
)"

if [[ "${current_branch}" != "${DEPLOY_BRANCH}" ]]; then
    echo "Expected branch ${DEPLOY_BRANCH}, found ${current_branch}." >&2
    exit 1
fi

git -C "${REPO_DIR}" pull \
    --ff-only \
    origin \
    "${DEPLOY_BRANCH}"

cd "${APP_DIR}"

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
