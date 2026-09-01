#!/usr/bin/env bash
# Emergency rollback: restore the newest /etc/caddy/Caddyfile.bak-* backup.
# Self-deletes after a successful reload (restored on next deploy).
#
#   sudo bash /var/www/safehouse-community-site/deploy/restore-caddy-backup.sh

set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: Caddy restore failed. Script kept:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

CADDYFILE="${CADDYFILE:-/etc/caddy/Caddyfile}"

if [[ "${EUID}" -ne 0 ]]; then
    exec sudo -E bash "$SCRIPT_PATH" "$@"
fi

mapfile -t BACKUPS < <(ls -1t "${CADDYFILE}.bak-"* 2>/dev/null || true)

if [[ ${#BACKUPS[@]} -eq 0 ]]; then
    echo "ERROR: No backups found matching ${CADDYFILE}.bak-*"
    exit 1
fi

LATEST="${BACKUPS[0]}"
echo "==> Restoring: $LATEST"
cp -a "$LATEST" "$CADDYFILE"

echo "==> Validating…"
caddy validate --config "$CADDYFILE"

echo "==> Reloading Caddy…"
systemctl reload caddy

echo "Done. Restored from $(basename "$LATEST")."
echo "If CMS works again, re-run apply-caddy-site-once.sh after the next deploy for Turnstile CSP."

trap - ERR
self_remove
echo "Script removed: $SCRIPT_PATH"
