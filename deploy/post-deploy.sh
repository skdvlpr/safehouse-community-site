#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
cd "$DEPLOY_PATH"

if [ ! -f .env ]; then
    echo "ERROR: $DEPLOY_PATH/.env is missing. Copy deploy/env.production.example and configure it first."
    exit 1
fi

php artisan migrate --force --no-interaction

php artisan optimize:clear --no-interaction

php artisan db:seed --class=RoleSeeder --force --no-interaction
php artisan db:seed --class=PageSeeder --force --no-interaction
php artisan db:seed --class=DeployArticleSeeder --force --no-interaction
php artisan db:seed --class=DonationCampaignSeeder --force --no-interaction
php artisan db:seed --class=DeploySiteContentSeeder --force --no-interaction
php artisan db:seed --class=DeployIntegrationSeeder --force --no-interaction

php artisan permission:cache-reset --no-interaction 2>/dev/null || true

php artisan storage:link --force 2>/dev/null || true

# Do not config:cache here — files are created as the deploy user and PHP-FPM (www-data)
# cannot read them without passwordless sudo. Filament/Livewire also misbehave with cached config.
php artisan config:clear --no-interaction
php artisan filament:optimize --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

# Ensure PHP-FPM (www-data) can read/write runtime dirs after CLI deploy.
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
if command -v sudo >/dev/null 2>&1; then
    sudo chown -R "${USER:-deploy}:www-data" storage bootstrap/cache 2>/dev/null || true
    sudo chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
fi

php artisan cms:health --no-interaction || true

if command -v sudo >/dev/null 2>&1; then
    if sudo -n -u www-data php artisan cms:health --no-interaction 2>/dev/null; then
        :
    else
        echo "WARN: Could not run cms:health as www-data (passwordless sudo missing)."
        echo "      After deploy, run on the server:"
        echo "        cd ${DEPLOY_PATH} && php artisan optimize:clear && sudo chown -R deploy:www-data storage bootstrap/cache && sudo chmod -R ug+rwx storage bootstrap/cache && sudo systemctl reload php8.3-fpm"
    fi
fi

echo "Deploy post-steps finished."
