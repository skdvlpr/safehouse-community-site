#!/usr/bin/env bash
# One-time production helper: sync safehouse.community Caddy block (Turnstile CSP).
# Self-deletes after a successful reload.
#
# Run on the CRM VPS after site deploy:
#   sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh
#
# Idempotent — safe to run again until it succeeds (then the file removes itself).

set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
DEPLOY_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CADDYFILE="${CADDYFILE:-/etc/caddy/Caddyfile}"
SNIPPET="${DEPLOY_PATH}/deploy/Caddyfile.snippet"

self_remove() {
    rm -f -- "$SCRIPT_PATH" 2>/dev/null || true
}

on_error() {
    echo ""
    echo "ERROR: Caddy update failed. Backup kept; script NOT removed:"
    echo "  $SCRIPT_PATH"
    exit 1
}

trap on_error ERR

if [[ "${EUID}" -ne 0 ]]; then
    exec sudo -E bash "$SCRIPT_PATH" "$@"
fi

if [[ ! -f "$SNIPPET" ]]; then
    echo "ERROR: Snippet not found: $SNIPPET"
    echo "Deploy the site first (rsync / GitHub Actions), then re-run."
    exit 1
fi

if [[ ! -f "$CADDYFILE" ]]; then
    echo "ERROR: Caddyfile not found: $CADDYFILE"
    exit 1
fi

if ! command -v caddy >/dev/null 2>&1; then
    echo "ERROR: caddy binary not found in PATH."
    exit 1
fi

TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP="${CADDYFILE}.bak-${TIMESTAMP}"
cp -a "$CADDYFILE" "$BACKUP"
echo "==> Backup: $BACKUP"

python3 - "$CADDYFILE" "$SNIPPET" <<'PY'
import re
import sys
from pathlib import Path

caddy_path = Path(sys.argv[1])
snippet_path = Path(sys.argv[2])

caddy = caddy_path.read_text(encoding="utf-8")
snippet = snippet_path.read_text(encoding="utf-8")

block_match = re.search(
    r"(?ms)^safehouse\.community.*?^\}",
    snippet,
)
if not block_match:
    raise SystemExit(f"No safehouse.community block in {snippet_path}")

new_block = block_match.group(0).rstrip() + "\n"

csp_match = re.search(
    r'^\s*Content-Security-Policy\s+"[^"]+"\s*$',
    new_block,
    re.MULTILINE,
)
if not csp_match:
    raise SystemExit("Content-Security-Policy line missing in snippet")

expected_csp = csp_match.group(0).strip()

if "challenges.cloudflare.com" in caddy and re.search(
    r"(?ms)^safehouse\.community.*?^\}",
    caddy,
):
    print("==> safehouse.community block already includes Turnstile CSP — validating only.")
    sys.exit(0)

existing = re.search(r"(?ms)^safehouse\.community.*?^\}", caddy)

if existing:
    print("==> Updating existing safehouse.community block from deploy/Caddyfile.snippet")
    updated = caddy[: existing.start()] + new_block + caddy[existing.end() :]
else:
    print("==> Appending safehouse.community block from deploy/Caddyfile.snippet")
    updated = caddy.rstrip() + "\n\n" + new_block

if "challenges.cloudflare.com" not in updated:
    if re.search(r'^\s*Content-Security-Policy\s+"', updated, re.MULTILINE):
        print("==> Patching Content-Security-Policy for Cloudflare Turnstile")
        updated = re.sub(
            r'^\s*Content-Security-Policy\s+"[^"]+"\s*$',
            expected_csp,
            updated,
            count=1,
            flags=re.MULTILINE,
        )
    else:
        raise SystemExit("Could not locate Content-Security-Policy to patch.")

caddy_path.write_text(updated, encoding="utf-8")
print("==> Caddyfile updated.")
PY

echo "==> Validating Caddy config…"
caddy validate --config "$CADDYFILE"

echo "==> Reloading Caddy…"
if command -v systemctl >/dev/null 2>&1; then
    systemctl reload caddy
else
    caddy reload --config "$CADDYFILE"
fi

echo ""
echo "Done. safehouse.community Caddy block is up to date (Turnstile CSP included)."
self_remove
echo "Script removed: $SCRIPT_PATH"
