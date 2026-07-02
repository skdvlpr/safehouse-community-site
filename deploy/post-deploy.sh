#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
cd "$DEPLOY_PATH"

if [ ! -f .env ]; then
    echo "ERROR: $DEPLOY_PATH/.env is missing. Copy deploy/env.production.example and configure it first."
    exit 1
fi

fix_runtime_permissions() {
    if command -v sudo >/dev/null 2>&1; then
        sudo chown -R "${USER:-deploy}:www-data" storage bootstrap/cache 2>/dev/null || true
    fi

    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

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

fix_runtime_permissions

php artisan migrate --force --no-interaction

php artisan optimize:clear --no-interaction

php artisan db:seed --class=RoleSeeder --force --no-interaction
php artisan db:seed --class=PageSeeder --force --no-interaction
php artisan db:seed --class=DeployArticleSeeder --force --no-interaction
php artisan db:seed --class=DonationCampaignSeeder --force --no-interaction
php artisan db:seed --class=DeploySiteContentSeeder --force --no-interaction

php artisan permission:cache-reset --no-interaction 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

# Do not config:cache here — files are created as the deploy user and PHP-FPM (www-data)
# cannot read them without passwordless sudo. Filament/Livewire also misbehave with cached config.
php artisan config:clear --no-interaction
php artisan filament:optimize --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

fix_runtime_permissions
php artisan view:clear --no-interaction

php artisan cms:health --no-interaction || true

if command -v sudo >/dev/null 2>&1; then
    if sudo -n -u www-data php artisan cms:health --no-interaction 2>/dev/null; then
        :
    else
        echo "WARN: Could not run cms:health as www-data (passwordless sudo missing)."
        echo "      After deploy, run on the server:"
        echo "        cd ${DEPLOY_PATH} && php artisan view:clear && sudo chown -R deploy:www-data storage bootstrap/cache && sudo chmod -R ug+rwX storage bootstrap/cache && sudo find storage/framework/views -type f -exec chmod 664 {} +"
    fi
fi

echo "Deploy post-steps finished."
