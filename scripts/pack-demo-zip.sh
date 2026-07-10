#!/usr/bin/env bash
# 30-day demo ZIP for GitHub Release / Packages.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
VER="${1:-dev}"
OUT="dist/tavle-cms-demo-30d-${VER}.zip"
mkdir -p dist
rm -f "$OUT"
zip -rq "$OUT" . \
  -x ".git/*" \
  -x "site/data/*.sqlite" \
  -x "site/data/*.sqlite-journal" \
  -x "site/data/*.db" \
  -x "site/uploads/cars/*" \
  -x "**/.env" \
  -x "**/.env.local" \
  -x "dist/*" \
  -x "*.zip"
echo "Created $OUT ($(du -h "$OUT" | cut -f1))"