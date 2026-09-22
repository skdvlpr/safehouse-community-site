#!/usr/bin/env bash
# One-shot production sync of cookie/privacy CMS bodies from LegalPagesContent.
# Marker in storage/ survives later rsync; the script itself stays in git.
#
# https://laravel.com/docs/13.x/artisan
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/safehouse-community-site}"
MARKER="${DEPLOY_PATH}/storage/app/legal-pages-synced-0025"

cd "$DEPLOY_PATH"

if [ -f "$MARKER" ]; then
    echo "Legal pages already synced for 002.5; skip."
    exit 0
fi

php artisan site:sync-legal-pages --force --no-interaction

mkdir -p "$(dirname "$MARKER")"
date -u +%Y-%m-%dT%H:%M:%SZ > "$MARKER"
echo "Legal pages synced. Marker: $MARKER"
