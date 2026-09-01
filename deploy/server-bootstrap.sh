#!/usr/bin/env bash
# One-time server prep for GitHub Actions / rsync deploy.
# Self-deletes after success (restored on next deploy).
#
# Run on 77.81.234.138 as root (or with sudo):
#   sudo bash /var/www/safehouse-community-site/deploy/server-bootstrap.sh

set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: Server bootstrap failed. Script kept:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

DEPLOY_USER="${DEPLOY_USER:-deploy}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
DB_NAME="${DB_NAME:-safehouse_community}"
DB_USER="${DB_USER:-safehouse_site}"

echo "==> Creating app directory ${DEPLOY_PATH} for user ${DEPLOY_USER}"
mkdir -p "${DEPLOY_PATH}"
chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "${DEPLOY_PATH}"

echo "==> MariaDB database ${DB_NAME} (set DB password manually if user is new)"
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';" || true
mysql -e "GRANT ALL ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

echo ""
echo "Done. Next steps:"
echo "  1. Re-run GitHub Actions deploy (or push to main)."
echo "  2. On server after first rsync:"
echo "       cd ${DEPLOY_PATH}"
echo "       cp deploy/env.production.example .env"
echo "       php artisan key:generate"
echo "       # edit .env — DB password, ESPOCRM_*, STRIPE_*"
echo "  3. Run apply-caddy-site-once.sh after deploy for Caddy block."

trap - ERR
self_remove
echo "Script removed: $SCRIPT_PATH"
