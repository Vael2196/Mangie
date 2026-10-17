#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${EUID}" -eq 0 ]]; then
    echo "Run this installer as the deployment user, not root." >&2
    exit 1
fi

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPO_DIR="$(cd "${APP_DIR}/.." && pwd)"
DEPLOY_USER="$(id --user --name)"
TEMPLATE_DIR="${APP_DIR}/deploy/systemd"
UNIT_DIR="/etc/systemd/system"
TEMP_DIR="$(mktemp --directory)"

cleanup() {
    rm -rf -- "${TEMP_DIR}"
}

trap cleanup EXIT

if [[ ! -f "${APP_DIR}/.env.production" ]]; then
    echo "Missing ${APP_DIR}/.env.production." >&2
    exit 1
fi

if ! command -v flock >/dev/null 2>&1; then
    echo "The flock command is required (provided by util-linux on Ubuntu)." >&2
    exit 1
fi

if ! docker info >/dev/null 2>&1 \
    && ! sudo -n docker info >/dev/null 2>&1; then
    echo "Automatic deployment needs Docker access without an interactive password." >&2
    echo "Add ${DEPLOY_USER} to the docker group or configure passwordless sudo for Docker." >&2
    exit 1
fi

if [[ "${APP_DIR}" == *"|"* || "${DEPLOY_USER}" == *"|"* ]]; then
    echo "The deployment path and user must not contain a pipe character." >&2
    exit 1
fi

sed \
    -e "s|@@DEPLOY_USER@@|${DEPLOY_USER}|g" \
    -e "s|@@APP_DIR@@|${APP_DIR}|g" \
    -e "s|@@REPO_DIR@@|${REPO_DIR}|g" \
    "${TEMPLATE_DIR}/mangie-autodeploy.service.in" \
    > "${TEMP_DIR}/mangie-autodeploy.service"

sudo install \
    --mode=0644 \
    "${TEMP_DIR}/mangie-autodeploy.service" \
    "${UNIT_DIR}/mangie-autodeploy.service"

sudo install \
    --mode=0644 \
    "${TEMPLATE_DIR}/mangie-autodeploy.timer" \
    "${UNIT_DIR}/mangie-autodeploy.timer"

sudo systemctl daemon-reload
sudo systemctl enable --now mangie-autodeploy.timer
sudo systemctl start mangie-autodeploy.service

sudo systemctl --no-pager status mangie-autodeploy.timer

echo
echo "Mangie will now check origin/master for updates every two minutes."
