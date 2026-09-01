#!/usr/bin/env bash
# Runs after every CI/rsync deploy. Self-deletes after success (restored on next deploy).
#
# Does NOT change PHP ini, Caddy, or one-time server settings — those are separate
# deploy/*-once.sh scripts run manually as root.
set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: Post-deploy failed. Script kept for retry:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
cd "$DEPLOY_PATH"

umask 0002

if [ ! -f .env ]; then
    echo "ERROR: $DEPLOY_PATH/.env is missing. Copy deploy/env.production.example and configure it first."
    exit 1
fi

fix_runtime_permissions() {
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
    chgrp -R www-data storage bootstrap/cache 2>/dev/null || true

    for dir in storage/framework/views storage/framework/cache storage/framework/sessions storage/logs storage/app/public storage/app/private/livewire-tmp; do
        mkdir -p "$dir"
        chmod 2775 "$dir" 2>/dev/null || true
    done
}

warm_cms_cache() {
    local host
    host="$(php -r 'echo parse_url(getenv("APP_URL") ?: "https://safehouse.community", PHP_URL_HOST) ?: "safehouse.community";')"

    # Must use -L: Caddy redirects HTTP→HTTPS; compiled views must be created by PHP-FPM (www-data),
    # not by artisan running as deploy — otherwise Blade touch() returns 500 for CMS.
    curl -sSfL -o /dev/null -H "Host: ${host}" "http://127.0.0.1/cms-safehouse/login" \
        || true
}

fix_runtime_permissions

php artisan migrate --force --no-interaction

php artisan optimize:clear --no-interaction

# Ensure CMS roles exist when new roles are added in code (firstOrCreate — safe to repeat).
php artisan db:seed --class=RoleSeeder --force --no-interaction

php artisan permission:cache-reset --no-interaction 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

# Do not config:cache — PHP-FPM (www-data) cannot read deploy-owned cache files.
php artisan config:clear --no-interaction
php artisan filament:optimize --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

warm_cms_cache

echo "Deploy post-steps finished."

trap - ERR
self_remove
echo "Script removed: $SCRIPT_PATH"
