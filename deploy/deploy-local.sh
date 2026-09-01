#!/usr/bin/env bash
# Manual deploy from your machine (WSL). Local only — NOT rsync'd to production.
#
# Usage:
#   export DEPLOY_HOST=77.81.234.138
#   export DEPLOY_USER=deploy
#   export DEPLOY_PATH=/var/www/safehouse-community-site
#   ./deploy/deploy-local.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEPLOY_HOST="${DEPLOY_HOST:-77.81.234.138}"
DEPLOY_USER="${DEPLOY_USER:-deploy}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
DEPLOY_SSH_PORT="${DEPLOY_SSH_PORT:-22}"
SSH_OPTS=(-p "$DEPLOY_SSH_PORT" -o StrictHostKeyChecking=accept-new)

echo "==> Building assets…"
if command -v ddev >/dev/null && ddev describe >/dev/null 2>&1; then
    ddev composer install --no-dev --optimize-autoloader --no-interaction
    ddev npm ci
    ddev npm run build
else
    composer install --no-dev --optimize-autoloader --no-interaction
    npm ci
    npm run build
fi

echo "==> Rsync to ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/"
rsync -rlptz --human-readable \
    -e "ssh ${SSH_OPTS[*]}" \
    --exclude-from=deploy/rsync-excludes.txt \
    ./ "${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/"

echo "==> Post-deploy on server…"
ssh "${SSH_OPTS[@]}" "${DEPLOY_USER}@${DEPLOY_HOST}" \
    "DEPLOY_PATH='${DEPLOY_PATH}' bash ${DEPLOY_PATH}/deploy/post-deploy.sh"

echo "Done. Test: curl -k -H 'Host: safehouse.community' https://${DEPLOY_HOST}/"
