#!/usr/bin/env bash
# Emergency rollback: restore the newest /etc/caddy/Caddyfile.bak-* backup.
# Does NOT self-delete — keep for repeated use.
#
#   sudo bash /var/www/safehouse-community-site/deploy/restore-caddy-backup.sh
#   sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh

set -euo pipefail

CADDYFILE="${CADDYFILE:-/etc/caddy/Caddyfile}"

if [[ "${EUID}" -ne 0 ]]; then
    exec sudo -E bash "$0" "$@"
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
echo "If CMS works again, re-run apply-caddy-site-once.sh (fixed version) for Turnstile CSP."
