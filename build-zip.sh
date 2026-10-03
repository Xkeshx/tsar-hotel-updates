#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VERSION="$(sed -n 's/^ \* Version: //p' "$ROOT/tsar-hotel-updates.php" | head -n 1)"
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Could not read a valid plugin version from tsar-hotel-updates.php" >&2
  exit 1
fi

OUT="${1:-$ROOT/dist/tsar-hotel-updates-v${VERSION}.zip}"
if [[ "$OUT" != /* ]]; then
  OUT="$ROOT/$OUT"
fi
mkdir -p "$(dirname "$OUT")"

python3 "$ROOT/tests/check_release.py"
if command -v php >/dev/null 2>&1; then
  php -l "$ROOT/tsar-hotel-updates.php"
  php -l "$ROOT/uninstall.php"
else
  echo "Note: PHP CLI is unavailable; PHP syntax lint was skipped."
fi

if ! command -v zip >/dev/null 2>&1; then
  echo "The 'zip' utility is required to build the plugin archive." >&2
  exit 1
fi

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
PLUGIN_DIR="$STAGE/tsar-hotel-updates"
mkdir -p "$PLUGIN_DIR/assets" "$PLUGIN_DIR/includes"
cp "$ROOT/tsar-hotel-updates.php" "$ROOT/uninstall.php" "$ROOT/readme.txt" "$ROOT/LICENSE.txt" "$PLUGIN_DIR/"
cp "$ROOT/assets/front.css" "$ROOT/assets/redesign.css" "$ROOT/assets/redesign.js" "$PLUGIN_DIR/assets/"
cp "$ROOT/includes/class-homepage-redesign.php" "$PLUGIN_DIR/includes/"
cp "$ROOT/README.md" "$ROOT/ADMIN-HANDOVER.md" "$ROOT/TEST-REPORT.md" "$ROOT/REDESIGN-PLAN.md" "$PLUGIN_DIR/"

rm -f "$OUT"
(
  cd "$STAGE"
  zip -qr "$OUT" tsar-hotel-updates
)
echo "Built $OUT"
