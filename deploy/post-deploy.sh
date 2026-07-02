#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
cd "$DEPLOY_PATH"

umask 0002

if [ ! -f .env ]; then
    echo "ERROR: $DEPLOY_PATH/.env is missing. Copy deploy/env.production.example and configure it first."
    exit 1
fi

fix_runtime_permissions() {
    if command -v sudo >/dev/null 2>&1; then
        sudo chown -R "${USER:-deploy}:www-data" storage bootstrap/cache 2>/dev/null || true
    fi

    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
    chgrp -R www-data storage bootstrap/cache 2>/dev/null || true

    for dir in storage/framework/views storage/framework/cache storage/logs; do
        mkdir -p "$dir"
        chmod 2775 "$dir" 2>/dev/null || true
        find "$dir" -type d -exec chmod 2775 {} + 2>/dev/null || true
        find "$dir" -type f -exec chmod 664 {} + 2>/dev/null || true
    done

    if command -v setfacl >/dev/null 2>&1; then
        setfacl -R -m g:www-data:rwX -d g:www-data:rwX storage/framework/views 2>/dev/null || true
        setfacl -R -m g:www-data:rwX -d g:www-data:rwX storage/framework/cache 2>/dev/null || true
    fi
}

warm_cms_cache() {
    local host
    host="$(php -r 'echo parse_url(getenv("APP_URL") ?: "https://safehouse.community", PHP_URL_HOST) ?: "safehouse.community";')"

    curl -sf -o /dev/null "https://${host}/cms-safehouse/login" \
        || curl -sf -o /dev/null -H "Host: ${host}" "http://127.0.0.1/cms-safehouse/login" \
        || true
}

fix_runtime_permissions

php artisan migrate --force --no-interaction

php artisan optimize:clear --no-interaction

# Only ensure RBAC roles exist. Never re-seed CMS content here — PageSeeder,
# DeployArticleSeeder, DonationCampaignSeeder and DeploySiteContentSeeder use
# updateOrCreate/updateMany and would wipe production edits on every deploy.
php artisan db:seed --class=RoleSeeder --force --no-interaction

php artisan permission:cache-reset --no-interaction 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

# Do not config:cache here — files are created as the deploy user and PHP-FPM (www-data)
# cannot read them without passwordless sudo. Filament/Livewire also misbehave with cached config.
php artisan config:clear --no-interaction
php artisan filament:optimize --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

# Drop deploy-owned Blade cache; warm CMS through PHP-FPM (www-data) instead of artisan cms:health.
php artisan view:clear --no-interaction
fix_runtime_permissions
warm_cms_cache
fix_runtime_permissions

echo "Deploy post-steps finished."
