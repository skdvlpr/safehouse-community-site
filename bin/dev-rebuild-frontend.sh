#!/usr/bin/env bash
# Rebuild Vite assets into public/build (gitignored).
# Run after pull or when public CSS/JS looks stale (e.g. missing .nav-link--highlight).
set -euo pipefail
cd "$(dirname "$0")/.."
if command -v ddev >/dev/null 2>&1 && ddev describe >/dev/null 2>&1; then
  ddev npm run build
else
  npm run build
fi
echo "Frontend build complete. Hard-refresh the browser if needed."
