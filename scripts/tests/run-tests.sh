#!/usr/bin/env bash
#
# Inner First Aid — full automated test suite.
#
# Prerequisites:
#   - A WordPress test site with the theme + IFA Core plugin active and pages built.
#   - PHP CLI (any PHP >= 7.4) and Node >= 18 with `npm install` done in this folder.
#
# Usage:
#   run-tests.sh <base_url> <wp_path> [admin_user] [admin_pass]
#
# Example:
#   bash scripts/tests/run-tests.sh http://127.0.0.1:8899 /path/to/wordpress
#
set -u
cd "$(dirname "$0")"

BASE="${1:-http://127.0.0.1:8899}"
WP_PATH="${2:-}"
ADMIN_USER="${3:-admin}"
ADMIN_PASS="${4:-AdminPass!234}"

echo "======================================================"
echo " Inner First Aid test suite"
echo " Target: $BASE"
echo "======================================================"

if command -v php >/dev/null 2>&1; then
  PHP_BIN=php
elif command -v php-wasm-cli >/dev/null 2>&1; then
  PHP_BIN=php-wasm-cli
else
  echo "No PHP CLI found — cannot run PHP lint." >&2
  PHP_BIN=""
fi

echo
echo "[1/4] PHP lint"
LINT_FAIL=0
if [ -n "$PHP_BIN" ]; then
  ROOT="$(cd ../../ && pwd)"
  while IFS= read -r -d '' f; do
    out=$("$PHP_BIN" -l "$f" 2>&1)
    if ! echo "$out" | grep -q "No syntax errors"; then
      echo "  LINT FAIL $f"; echo "$out"; LINT_FAIL=1
    fi
  done < <(find "$ROOT/wp-content" -name '*.php' -print0)
  [ "$LINT_FAIL" = "0" ] && echo "  All PHP files OK"
else
  echo "  skipped (no PHP)"
fi

echo
echo "[2/4] Preparing test state (Stripe links + rate limits)"
if [ -n "$WP_PATH" ] && [ -f "$WP_PATH/wp-load.php" ]; then
  PHP_BIN="$(command -v php || command -v php-wasm-cli || true)"
  if [ -n "$PHP_BIN" ]; then
    (cd "$WP_PATH" && "$PHP_BIN" -r '
      $_SERVER["HTTP_HOST"]="x"; $_SERVER["REQUEST_URI"]="/";
      require "wp-load.php";
      if (function_exists("ifa_update_option")) {
        ifa_update_option("stripe_en",  "https://buy.stripe.com/EN123");
        ifa_update_option("stripe_sl_f","https://buy.stripe.com/SLF456");
        ifa_update_option("stripe_sl_m","https://buy.stripe.com/SLM789");
      }
      global $wpdb;
      $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE \"_transient_ifa_rl_%\" OR option_name LIKE \"_transient_timeout_ifa_rl_%\"");
      echo "test state prepared\n";
    ' 2>/dev/null)
  fi
fi

echo
echo "[3/4] HTTP tests"
bash ./http-tests.sh "$BASE" "$WP_PATH" "$ADMIN_USER" "$ADMIN_PASS" || HTTP_FAIL=1
HTTP_FAIL="${HTTP_FAIL:-0}"

echo
echo "[4/4] DOM interaction tests"
if [ ! -d node_modules ]; then
  echo "  Installing jsdom..."
  npm install --no-audit --no-fund >/dev/null 2>&1
fi
node dom-tests.mjs "$BASE" || DOM_FAIL=1
DOM_FAIL="${DOM_FAIL:-0}"

echo
echo "======================================================"
if [ "$LINT_FAIL" = "1" ] || [ "$HTTP_FAIL" = "1" ] || [ "$DOM_FAIL" = "1" ]; then
  echo "RESULT: FAILURES PRESENT"
  exit 1
fi
echo "RESULT: ALL TESTS PASSED"
echo "======================================================"
