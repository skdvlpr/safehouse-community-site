#!/usr/bin/env bash
# Fix Laravel/Livewire storage permissions on the VPS (run once as root).
# Self-deletes after success.
#
#   sudo bash /var/www/safehouse-community-site/deploy/fix-storage-permissions-once.sh
#
set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: Storage permissions were NOT fixed. Script kept:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root: sudo bash $SCRIPT_PATH"
    exit 1
fi

APP="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
DEPLOY_USER="${DEPLOY_USER:-deploy}"

cd "$APP"

chown -R "${DEPLOY_USER}:www-data" storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

for dir in \
    storage/framework/cache \
    storage/framework/sessions \
    storage/logs \
    storage/app/public \
    storage/app/private/livewire-tmp; do
    mkdir -p "$dir"
    chmod 2775 "$dir"
    chown "${DEPLOY_USER}:www-data" "$dir"
done

# Blade cache must be owned by www-data (PHP-FPM). deploy-owned files → CMS 500 (touch utime).
mkdir -p storage/framework/views
chmod 2775 storage/framework/views
chown www-data:www-data storage/framework/views
find storage/framework/views -mindepth 1 -delete 2>/dev/null || true

for dir in storage/app/public/article-carousels storage/app/public/page-carousels; do
    mkdir -p "$dir"
    chmod 2775 "$dir"
    chown www-data:www-data "$dir"
done

if command -v setfacl >/dev/null 2>&1; then
    setfacl -R -m g:www-data:rwX -d g:www-data:rwX storage/app/public 2>/dev/null || true
    setfacl -R -m g:www-data:rwX -d g:www-data:rwX storage/app/private/livewire-tmp 2>/dev/null || true
fi

php artisan storage:link --force 2>/dev/null || true

# Warm CMS so www-data compiles Blade views (same as post-deploy).
host="$(php -r 'echo parse_url(getenv("APP_URL") ?: "https://safehouse.community", PHP_URL_HOST) ?: "safehouse.community";')"
curl -sSfL -o /dev/null -H "Host: ${host}" "http://127.0.0.1/cms-safehouse/login" || true

echo "Storage permissions fixed under ${APP}"

trap - ERR
self_remove
echo "Script removed: $SCRIPT_PATH"
