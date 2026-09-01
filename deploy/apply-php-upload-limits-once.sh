#!/usr/bin/env bash
# Raise PHP-FPM upload limits for CMS carousel uploads (run once as root on the VPS).
# Self-deletes after a successful reload.
#
#   sudo bash /var/www/safehouse-community-site/deploy/apply-php-upload-limits-once.sh
#
set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: PHP upload limits were NOT applied. Script kept:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root: sudo bash $SCRIPT_PATH"
    exit 1
fi

SOURCE="${DEPLOY_PATH:-/var/www/safehouse-community-site}/deploy/php-fpm-upload-limits.ini"
TARGET="/etc/php/8.4/fpm/conf.d/99-safehouse-upload-limits.ini"
CLI_TARGET="/etc/php/8.4/cli/conf.d/99-safehouse-upload-limits.ini"

if [ ! -f "$SOURCE" ]; then
    echo "Missing $SOURCE — deploy the repo first."
    exit 1
fi

install -m 644 "$SOURCE" "$TARGET"
install -m 644 "$SOURCE" "$CLI_TARGET"

php-fpm8.4 -t
systemctl reload php8.4-fpm

echo "PHP upload limits applied:"
php -r 'foreach (["upload_max_filesize","post_max_size","memory_limit"] as $k) { echo "  $k=".ini_get($k).PHP_EOL; }'

trap - ERR
self_remove
echo "Script removed: $SCRIPT_PATH"
