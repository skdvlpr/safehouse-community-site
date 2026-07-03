#!/usr/bin/env bash
# Repair or apply safehouse.community Caddy block (Turnstile CSP, CMS-safe headers).
# Self-deletes after a successful reload.
#
# If CMS broke after a bad Caddy edit, restore the latest backup first:
#   sudo ls -lt /etc/caddy/Caddyfile.bak-* | head
#   sudo cp /etc/caddy/Caddyfile.bak-YYYYMMDD-HHMMSS /etc/caddy/Caddyfile
#
# Then run:
#   sudo bash /var/www/safehouse-community-site/deploy/apply-caddy-site-once.sh

set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
DEPLOY_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CADDYFILE="${CADDYFILE:-/etc/caddy/Caddyfile}"
SNIPPET="${DEPLOY_PATH}/deploy/Caddyfile.snippet"
SITE_LABEL="safehouse.community"

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

python3 - "$CADDYFILE" "$SNIPPET" "$SITE_LABEL" <<'PY'
import re
import sys
from pathlib import Path


def extract_site_block(text: str, label: str) -> tuple[int, int] | None:
    match = re.search(rf"(?m)^{re.escape(label)}", text)
    if not match:
        return None

    brace_start = text.find("{", match.end())
    if brace_start == -1:
        return None

    depth = 0
    for index in range(brace_start, len(text)):
        char = text[index]
        if char == "{":
            depth += 1
        elif char == "}":
            depth -= 1
            if depth == 0:
                return match.start(), index + 1

    return None


def extra_cms_allowlist_ips(block: str) -> list[str]:
    ips: list[str] = []

    for line in block.splitlines():
        stripped = line.strip()
        if not stripped.startswith("not remote_ip"):
            continue

        for ip in stripped.split()[2:]:
            if ip.startswith("#"):
                break
            if ip not in {"127.0.0.1", "::1"}:
                ips.append(ip)

    return ips


def merge_cms_allowlist(block: str, ips: list[str]) -> str:
    if not ips:
        return block

    lines = block.splitlines()
    output: list[str] = []
    in_blocked = False
    inserted = False

    for line in lines:
        if "@blocked_cms" in line:
            in_blocked = True

        output.append(line)

        if in_blocked and not inserted and "not remote_ip 127.0.0.1 ::1" in line:
            for ip in ips:
                if f"not remote_ip {ip}" not in block:
                    output.append(f"        not remote_ip {ip}")
            inserted = True

        if in_blocked and line.strip() == "}":
            in_blocked = False

    return "\n".join(output) + ("\n" if block.endswith("\n") else "")


caddy_path = Path(sys.argv[1])
snippet_path = Path(sys.argv[2])
site_label = sys.argv[3]

caddy = caddy_path.read_text(encoding="utf-8")
snippet = snippet_path.read_text(encoding="utf-8")

new_span = extract_site_block(snippet, site_label)
if new_span is None:
    raise SystemExit(f"No {site_label} block in {snippet_path}")

new_block = snippet[new_span[0] : new_span[1]].rstrip() + "\n"

existing_span = extract_site_block(caddy, site_label)
if existing_span is not None:
    old_block = caddy[existing_span[0] : existing_span[1]]
    allowlist_ips = extra_cms_allowlist_ips(old_block)
    if allowlist_ips:
        print(f"==> Preserving CMS allowlist IPs: {', '.join(allowlist_ips)}")
        new_block = merge_cms_allowlist(new_block, allowlist_ips)

    print("==> Replacing safehouse.community block from deploy/Caddyfile.snippet")
    updated = caddy[: existing_span[0]] + new_block + caddy[existing_span[1] :]
else:
    print("==> Appending safehouse.community block from deploy/Caddyfile.snippet")
    updated = caddy.rstrip() + "\n\n" + new_block

if "@public_csp not path /cms-safehouse*" not in updated:
    raise SystemExit("Snippet is missing the CMS-safe @public_csp matcher.")

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
echo "Done. safehouse.community Caddy block restored (CMS excluded from CSP)."
self_remove
echo "Script removed: $SCRIPT_PATH"
