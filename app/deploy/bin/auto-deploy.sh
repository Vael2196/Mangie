#!/usr/bin/env bash

set -Eeuo pipefail

if [[ "${EUID}" -eq 0 ]]; then
    echo "Run this script as the deployment user, not root." >&2
    exit 1
fi

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPO_DIR="$(cd "${APP_DIR}/.." && pwd)"
DEPLOY_BRANCH="${MANGIE_DEPLOY_BRANCH:-master}"

current_branch="$(
    git -C "${REPO_DIR}" symbolic-ref --quiet --short HEAD
)"

if [[ "${current_branch}" != "${DEPLOY_BRANCH}" ]]; then
    echo "Expected branch ${DEPLOY_BRANCH}, found ${current_branch}." >&2
    exit 1
fi

git -C "${REPO_DIR}" fetch \
    --quiet \
    --prune \
    origin \
    "${DEPLOY_BRANCH}"

local_revision="$(git -C "${REPO_DIR}" rev-parse HEAD)"
remote_revision="$(
    git -C "${REPO_DIR}" rev-parse "origin/${DEPLOY_BRANCH}"
)"

if [[ "${local_revision}" == "${remote_revision}" ]]; then
    echo "Mangie is already current at ${local_revision}."
    exit 0
fi

if ! git -C "${REPO_DIR}" merge-base \
    --is-ancestor \
    "${local_revision}" \
    "${remote_revision}"; then
    echo "Local ${DEPLOY_BRANCH} has diverged from origin; refusing automatic deployment." >&2
    exit 1
fi

echo "Deploying Mangie ${local_revision} -> ${remote_revision}."

exec "${APP_DIR}/deploy/bin/deploy.sh"
