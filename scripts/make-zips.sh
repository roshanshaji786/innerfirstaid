#!/usr/bin/env bash
#
# Create upload-ready ZIP files for easy installation:
#   dist/inner-first-aid.zip   -> theme  (Appearance > Themes > Upload)
#   dist/ifa-core.zip          -> plugin (Plugins > Add New > Upload)
#
# Usage: bash scripts/make-zips.sh
#
set -eu
cd "$(dirname "$0")/.."
ROOT="$(pwd)"
OUT="$ROOT/dist"
mkdir -p "$OUT"

if ! command -v zip >/dev/null 2>&1; then
  echo "zip is required. Install it (e.g. apt-get install zip) and re-run." >&2
  exit 1
fi

echo "Creating dist/inner-first-aid.zip ..."
(cd "$ROOT/wp-content/themes" && zip -qr "$OUT/inner-first-aid.zip" inner-first-aid)

echo "Creating dist/ifa-core.zip ..."
(cd "$ROOT/wp-content/plugins" && zip -qr "$OUT/ifa-core.zip" ifa-core)

echo
echo "Done! Upload these two files to WordPress:"
echo "  1. $OUT/inner-first-aid.zip"
echo "  2. $OUT/ifa-core.zip"
ls -lh "$OUT"
