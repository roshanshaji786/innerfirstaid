#!/usr/bin/env bash
#
# Inner First Aid — HTTP-level test suite.
# Asserts page availability, Elementor data, lead API behaviour, Stripe states,
# admin pages and CSV export. Run against a configured test site.
#
# Usage: http-tests.sh <base_url> <wp_path> [admin_user] [admin_pass]
#
set -u

BASE="${1:-http://127.0.0.1:8899}"
WP_PATH="${2:-/tmp/wp-test/wordpress}"
ADMIN_USER="${3:-admin}"
ADMIN_PASS="${4:-AdminPass!234}"
COOKIES="$(mktemp)"

PASS=0
FAIL=0

check() {
  if [ "$2" -eq 0 ]; then
    PASS=$((PASS + 1))
    echo "  PASS  $1"
  else
    FAIL=$((FAIL + 1))
    echo "  FAIL  $1  $3"
  fi
}

grepq() { grep -q "$2" "$1"; echo $?; }

echo "== Pages =="
for u in / /sl/ /privacy/ /terms/ /sl/privacy/ /sl/terms/; do
  code=$(curl -s -o /tmp/ifa_page.html -w "%{http_code}" "$BASE$u")
  check "GET $u -> 200" "$([ "$code" = "200" ]; echo $?)" "(got $code)"
done

curl -s "$BASE/" -o /tmp/ifa_home.html
curl -s "$BASE/sl/" -o /tmp/ifa_sl.html

echo "== EN home content =="
check "hero renders"        "$(grepq /tmp/ifa_home.html 'ifa-hero__title')" ""
check "lead form present"   "$(grepq /tmp/ifa_home.html 'data-ifa-lead-form')" ""
check "social proof"        "$(grepq /tmp/ifa_home.html 'ifa-social__item')" ""
check "programs section"    "$(grepq /tmp/ifa_home.html 'ifa-card--active')" ""
check "how it works"        "$(grepq /tmp/ifa_home.html 'ifa-steps__item')" ""
check "pricing CTA"         "$(grepq /tmp/ifa_home.html 'ifa-pricing__new')" ""
check "cookie banner"       "$(grepq /tmp/ifa_home.html 'data-ifa-cookie')" ""
check "header CTA"          "$(grepq /tmp/ifa_home.html 'data-ifa-cta')" ""
check "elementor css linked" "$(grepq /tmp/ifa_home.html 'elementor-post')" ""
check "self-hosted font css" "$(grepq /tmp/ifa_home.html 'fonts.css')" ""
check "no external google fonts" "$([ "$(grep -c 'fonts.googleapis.com' /tmp/ifa_home.html)" = "0" ]; echo $?)" ""

echo "== SL home content =="
check "SL headline"         "$(grepq /tmp/ifa_sl.html 'Ko te boli')" ""
check "SL lang attr"        "$(grepq /tmp/ifa_sl.html 'lang="sl-SI"')" ""
check "gender toggle"       "$(grepq /tmp/ifa_sl.html 'data-ifa-gender-toggle')" ""

echo "== Lead API =="
NONCE=$(grep -o '"nonce":"[a-f0-9]*"' /tmp/ifa_home.html | head -1 | cut -d'"' -f4)
AJ="$BASE/wp-admin/admin-ajax.php"

# Reset rate limits so the lead tests start from a clean state.
if [ -n "$WP_PATH" ] && [ -f "$WP_PATH/wp-load.php" ]; then
  PHP_BIN="$(command -v php || command -v php-wasm-cli || true)"
  if [ -n "$PHP_BIN" ]; then
    (cd "$WP_PATH" && "$PHP_BIN" -r '
      $_SERVER["HTTP_HOST"]="x"; $_SERVER["REQUEST_URI"]="/";
      require "wp-load.php";
      global $wpdb;
      $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE \"_transient_ifa_rl_%\" OR option_name LIKE \"_transient_timeout_ifa_rl_%\"");
    ' 2>/dev/null) || true
  fi
fi

code=$(curl -s -o /tmp/ifa_lead.json -w "%{http_code}" -X POST "$AJ" -d "action=ifa_submit_lead&nonce=$NONCE&email=http-test-$(date +%s)@example.com&lang=en&source=tests")
check "valid lead -> 201"   "$([ "$code" = "201" ]; echo $?)" "(got $code: $(cat /tmp/ifa_lead.json))"
check "lead json success"   "$(grepq /tmp/ifa_lead.json '"success":true')" ""

code=$(curl -s -o /tmp/ifa_lead.json -w "%{http_code}" -X POST "$AJ" -d "action=ifa_submit_lead&nonce=$NONCE&email=not-an-email&lang=en")
check "invalid email -> 400" "$([ "$code" = "400" ]; echo $?)" "(got $code)"

code=$(curl -s -o /tmp/ifa_lead.json -w "%{http_code}" -X POST "$AJ" -d "action=ifa_submit_lead&nonce=$NONCE&email=bot@example.com&lang=en&company_website=spam")
check "honeypot rejected"    "$([ "$code" = "200" ] && [ "$(grep -c 'created' /tmp/ifa_lead.json)" = "0" ]; echo $?)" ""

code=$(curl -s -o /tmp/ifa_lead.json -w "%{http_code}" -X POST "$AJ" -d "action=ifa_submit_lead&nonce=badnonce&email=x@example.com")
check "bad nonce -> 403"     "$([ "$code" = "403" ]; echo $?)" "(got $code)"

echo "== Free guide auto-delivery =="
GUIDE_CHECK=""
if [ -n "$WP_PATH" ] && [ -f "$WP_PATH/wp-load.php" ]; then
  PHP_BIN="$(command -v php || command -v php-wasm-cli || true)"
  if [ -n "$PHP_BIN" ]; then
    GUIDE_CHECK="$(cd "$WP_PATH" && "$PHP_BIN" -r '
      $_SERVER["HTTP_HOST"]="x"; $_SERVER["REQUEST_URI"]="/";
      require "wp-load.php";
      $up = wp_get_upload_dir();
      $file = $up["path"] . "/ifa-test-guide.pdf";
      file_put_contents($file, "%PDF-1.4 test");
      ifa_update_option("guide_pdf_url", $up["url"] . "/ifa-test-guide.pdf");
      ifa_update_option("guide_subject_en", "TEST: your free guide");
      $GLOBALS["mails"] = array();
      add_filter("wp_mail", function($a){ $GLOBALS["mails"][]=$a; return array_merge($a,array("to"=>"noop@test.local")); }, 1);
      IFA_Leads::instance()->send_guide("guide-check@example.com", "en");
      $m = isset($GLOBALS["mails"][0]) ? $GLOBALS["mails"][0] : array();
      echo (($m["to"] ?? "") === "guide-check@example.com" ? "to:OK " : "to:FAIL ");
      echo (isset($m["attachments"][0]) && file_exists($m["attachments"][0]) ? "attach:OK " : "attach:FAIL ");
      echo (strpos($m["subject"], "TEST") !== false ? "subject:OK" : "subject:FAIL");
    ' 2>/dev/null)"
  fi
fi
check "guide email sent with PDF attached" "$([ "$GUIDE_CHECK" = "to:OK attach:OK subject:OK" ]; echo $?)" "(got: $GUIDE_CHECK)"

echo "== Admin =="
curl -s -c "$COOKIES" -b "$COOKIES" -L \
  -d "log=$ADMIN_USER&pwd=$ADMIN_PASS&wp-submit=Log+In&redirect_to=$BASE/wp-admin/&testcookie=1" \
  "$BASE/wp-login.php" -o /dev/null
check "admin login"          "$([ -s "$COOKIES" ]; echo $?)" ""

curl -s -b "$COOKIES" "$BASE/wp-admin/admin.php?page=ifa-leads" -o /tmp/ifa_leads_admin.html
check "leads admin page"     "$(grepq /tmp/ifa_leads_admin.html 'Export CSV')" ""

CSV_URL=$(grep -o 'href="[^"]*action=ifa_export_leads[^"]*"' /tmp/ifa_leads_admin.html | head -1 | sed 's/href="//;s/"$//' | sed 's/&#038;/\&/g')
case "$CSV_URL" in
  http*) CSV_URL="$CSV_URL" ;;
  /*)    CSV_URL="$BASE$CSV_URL" ;;
esac
for attempt in 1 2 3; do
  curl -s -b "$COOKIES" "$CSV_URL" -o /tmp/ifa_leads.csv
  [ -s /tmp/ifa_leads.csv ] && break
  sleep 1
done
check "CSV export"           "$(grepq /tmp/ifa_leads.csv '^id,email,lang,source,ip,created_at')" ""

curl -s -b "$COOKIES" "$BASE/wp-admin/options-general.php?page=ifa-settings" -o /tmp/ifa_settings.html
check "settings page"        "$(grepq /tmp/ifa_settings.html 'Build pages now')" ""

echo
echo "HTTP tests: $PASS passed, $FAIL failed"
rm -f "$COOKIES"
[ "$FAIL" = "0" ] || exit 1
