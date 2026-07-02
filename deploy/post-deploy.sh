#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
cd "$DEPLOY_PATH"

if [ ! -f .env ]; then
    echo "ERROR: $DEPLOY_PATH/.env is missing. Copy deploy/env.production.example and configure it first."
    exit 1
fi

php artisan migrate --force --no-interaction

php artisan db:seed --class=RoleSeeder --force --no-interaction
php artisan db:seed --class=PageSeeder --force --no-interaction
php artisan db:seed --class=DeployArticleSeeder --force --no-interaction
php artisan db:seed --class=DonationCampaignSeeder --force --no-interaction
php artisan db:seed --class=DeployIntegrationSeeder --force --no-interaction

php artisan cache:clear --no-interaction

php artisan storage:link --force 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deploy post-steps finished."
