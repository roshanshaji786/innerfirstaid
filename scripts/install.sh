#!/usr/bin/env bash
#
# Inner First Aid — one-command local installer.
#
# Downloads WordPress + Elementor, installs them, activates the theme and the
# IFA Core plugin, and builds all pages as editable Elementor documents.
#
# Requirements: PHP >= 7.4 CLI with curl/mbstring/xml/zip + (MySQL/MariaDB OR SQLite)
# On the web: install WordPress + Elementor, upload wp-content/, click
# "Build pages now" in Settings > Inner First Aid.
#
# Usage:
#   bash scripts/install.sh [--db mysql|sqlite] [--base-url http://localhost:8080]
#
set -u
cd "$(dirname "$0")/.."
ROOT="$(pwd)"

DB="${DB:-sqlite}"
BASE_URL="${BASE_URL:-http://localhost:8080}"
WP_VERSION="${WP_VERSION:-latest}"
DOCROOT="$ROOT/wordpress"
DOWNLOAD_DIR="$ROOT/.downloads"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASS="${ADMIN_PASS:-AdminPass!234}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@innerfirstaid.test}"

for arg in "$@"; do
  case "$arg" in
    --db=*) DB="${arg#--db=}" ;;
    --base-url=*) BASE_URL="${arg#--base-url=}" ;;
  esac
done

mkdir -p "$DOWNLOAD_DIR"

say() { printf '\n\033[1;32m== %s ==\033[0m\n' "$*"; }
die() { printf '\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }

command -v curl >/dev/null 2>&1 || die "curl is required."
if [ -n "${PHP_BIN:-}" ]; then
  PHP="$PHP_BIN"
elif command -v php >/dev/null 2>&1; then
  PHP="$(command -v php)"
elif command -v php-wasm-cli >/dev/null 2>&1; then
  PHP="$(command -v php-wasm-cli)"
else
  die "PHP CLI is required (php >= 7.4). Set PHP_BIN=/path/to/php if it is not on PATH."
fi
WPCLI="$(command -v wp || true)"

# ---------------------------------------------------------------------------
say "1/7 Downloading WordPress $WP_VERSION"
if [ ! -d "$DOCROOT/wp-admin" ]; then
  URL="https://wordpress.org/latest.tar.gz"
  if ! curl -fsSL --max-time 120 "$URL" -o "$DOWNLOAD_DIR/wp.tar.gz"; then
    echo "  wordpress.org unreachable — falling back to the GitHub mirror."
    VER="${WP_VERSION}"
    if [ "$VER" = "latest" ]; then
      VER="$(curl -fsSL --max-time 30 "https://api.github.com/repos/WordPress/WordPress/tags?per_page=1" | grep -o '"name": *"[^"]*"' | head -1 | cut -d'"' -f4)"
    fi
    [ -n "$VER" ] || die "Could not resolve the latest WordPress version."
    curl -fsSL --max-time 180 "https://codeload.github.com/WordPress/WordPress/tar.gz/refs/tags/$VER" -o "$DOWNLOAD_DIR/wp.tar.gz" \
      || die "Could not download WordPress."
  fi
  mkdir -p "$DOCROOT"
  tar xzf "$DOWNLOAD_DIR/wp.tar.gz" -C "$DOCROOT" --strip-components=1 || die "Could not extract WordPress."
  echo "  WordPress extracted to $DOCROOT"
else
  echo "  already present, skipping."
fi

# ---------------------------------------------------------------------------
say "2/7 Configuring wp-config.php"
if [ ! -f "$DOCROOT/wp-config.php" ]; then
  SALTS="$("$PHP" -r 'foreach(["AUTH_KEY","SECURE_AUTH_KEY","LOGGED_IN_KEY","NONCE_KEY","AUTH_SALT","SECURE_AUTH_SALT","LOGGED_IN_SALT","NONCE_SALT"] as $k){echo "define(\x27$k\x27, \x27".bin2hex(random_bytes(24))."\x27);\n";}')"
  {
    echo "<?php"
    if [ "$DB" = "sqlite" ]; then
      echo "define( 'DB_ENGINE', 'sqlite' );"
      echo "define( 'DB_NAME', 'innerfirstaid' );"
      echo "define( 'DB_USER', 'root' );"
      echo "define( 'DB_PASSWORD', '' );"
      echo "define( 'DB_HOST', 'localhost' );"
    else
      echo "define( 'DB_NAME', '${DB_NAME:-innerfirstaid}' );"
      echo "define( 'DB_USER', '${DB_USER:-root}' );"
      echo "define( 'DB_PASSWORD', '${DB_PASSWORD:-}' );"
      echo "define( 'DB_HOST', '${DB_HOST:-localhost}' );"
    fi
    echo "define( 'DB_CHARSET', 'utf8' );"
    echo "define( 'DB_COLLATE', '' );"
    echo "$SALTS"
    echo "\$table_prefix = 'wp_';"
    echo "define( 'WP_DEBUG', false );"
    echo "define( 'WP_HOME', '$BASE_URL' );"
    echo "define( 'WP_SITEURL', '$BASE_URL' );"
    echo "if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }"
    echo "require_once ABSPATH . 'wp-settings.php';"
  } > "$DOCROOT/wp-config.php"
  echo "  wp-config.php written (engine: $DB)."
fi

# ---------------------------------------------------------------------------
say "3/7 Installing WordPress"
if [ "$DB" = "sqlite" ]; then
  # SQLite drop-in (no MySQL server needed).
  if [ ! -d "$DOCROOT/wp-content/plugins/sqlite-database-integration" ]; then
    mkdir -p "$DOCROOT/wp-content/plugins"
    if ! curl -fsSL --max-time 120 "https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip" -o "$DOWNLOAD_DIR/sqlite.zip"; then
      echo "  wordpress.org unreachable — falling back to the GitHub mirror."
      curl -fsSL --max-time 120 "https://codeload.github.com/WordPress/sqlite-database-integration/tar.gz/refs/heads/main" -o "$DOWNLOAD_DIR/sqlite.tar.gz" || die "Could not download the SQLite integration plugin."
      mkdir -p "$DOCROOT/wp-content/plugins/sqlite-database-integration"
      tar xzf "$DOWNLOAD_DIR/sqlite.tar.gz" -C "$DOCROOT/wp-content/plugins/sqlite-database-integration" --strip-components=1
    else
      (command -v unzip >/dev/null && unzip -q -o "$DOWNLOAD_DIR/sqlite.zip" -d "$DOCROOT/wp-content/plugins/") || die "unzip required."
    fi
  fi
  cp "$DOCROOT/wp-content/plugins/sqlite-database-integration/db.copy" "$DOCROOT/wp-content/db.php"
fi

if [ -n "$WPCLI" ]; then
  $WPCLI --path="$DOCROOT" core is-installed 2>/dev/null \
    || $WPCLI --path="$DOCROOT" core install --url="$BASE_URL" --title="Inner First Aid" \
        --admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" --admin_email="$ADMIN_EMAIL" --skip-email
else
  cat > "$DOCROOT/ifa-install.php" <<'PHPEOF'
<?php
define('WP_INSTALLING', true);
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if ( ! get_option('ifa_installed_flag') ) {
    $r = wp_install('Inner First Aid', getenv('IFA_ADMIN_USER') ?: 'admin', getenv('IFA_ADMIN_EMAIL') ?: 'admin@innerfirstaid.test', true, '', getenv('IFA_ADMIN_PASS') ?: 'AdminPass!234');
    update_option('ifa_installed_flag', 1);
    echo "Installed. User ID: " . (int) $r['user_id'] . "\n";
} else { echo "Already installed.\n"; }
PHPEOF
  (cd "$DOCROOT" && IFA_ADMIN_USER="$ADMIN_USER" IFA_ADMIN_PASS="$ADMIN_PASS" IFA_ADMIN_EMAIL="$ADMIN_EMAIL" "$PHP" ifa-install.php) \
    || die "WordPress install failed."
  rm -f "$DOCROOT/ifa-install.php"
fi
echo "  WordPress installed."

# ---------------------------------------------------------------------------
say "4/7 Downloading Elementor (free)"
if [ ! -d "$DOCROOT/wp-content/plugins/elementor" ]; then
  if ! curl -fsSL --max-time 180 "https://downloads.wordpress.org/plugin/elementor.latest-stable.zip" -o "$DOWNLOAD_DIR/elementor.zip"; then
    echo "  wordpress.org unreachable — falling back to the GitHub mirror."
    ELEM_TAG="$(curl -fsSL --max-time 30 "https://api.github.com/repos/elementor/elementor/tags?per_page=1" | grep -o '"name": *"[^"]*"' | head -1 | cut -d'"' -f4)"
    [ -n "$ELEM_TAG" ] || die "Could not resolve the latest Elementor tag."
    curl -fsSL --max-time 180 "https://codeload.github.com/elementor/elementor/tar.gz/refs/tags/$ELEM_TAG" -o "$DOWNLOAD_DIR/elementor.tar.gz" || die "Could not download Elementor."
    mkdir -p "$DOCROOT/wp-content/plugins/elementor"
    tar xzf "$DOWNLOAD_DIR/elementor.tar.gz" -C "$DOCROOT/wp-content/plugins/elementor" --strip-components=1
  else
    (command -v unzip >/dev/null && unzip -q -o "$DOWNLOAD_DIR/elementor.zip" -d "$DOCROOT/wp-content/plugins/") || die "unzip required."
  fi
  echo "  Elementor downloaded."
else
  echo "  already present, skipping."
fi

# ---------------------------------------------------------------------------
say "5/7 Installing theme + plugin"
cp -R "$ROOT/wp-content/themes/inner-first-aid" "$DOCROOT/wp-content/themes/"
cp -R "$ROOT/wp-content/plugins/ifa-core" "$DOCROOT/wp-content/plugins/"
echo "  copied."

# ---------------------------------------------------------------------------
say "6/7 Activating plugins and theme"
if [ -n "$WPCLI" ]; then
  $WPCLI --path="$DOCROOT" plugin activate sqlite-database-integration elementor ifa-core 2>/dev/null || true
  $WPCLI --path="$DOCROOT" theme activate inner-first-aid
else
  cat > "$DOCROOT/ifa-activate.php" <<'PHPEOF'
<?php
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
foreach (array('sqlite-database-integration/load.php', 'elementor/elementor.php', 'ifa-core/ifa-core.php') as $p) {
    if (file_exists(WP_PLUGIN_DIR . '/' . $p) && is_plugin_inactive($p)) {
        $r = activate_plugin($p);
        if (is_wp_error($r)) { echo "FAIL $p: {$r->get_error_message()}\n"; }
    }
}
switch_theme('inner-first-aid');
echo "activated\n";
PHPEOF
  (cd "$DOCROOT" && "$PHP" ifa-activate.php) || die "Activation failed."
  rm -f "$DOCROOT/ifa-activate.php"
fi

# ---------------------------------------------------------------------------
say "7/7 Building pages (Elementor)"
if [ -n "$WPCLI" ]; then
  $WPCLI --path="$DOCROOT" eval 'IFA_Builder::build_all(); echo "pages built\n";' || die "Page build failed."
else
  cat > "$DOCROOT/ifa-build.php" <<'PHPEOF'
<?php
require __DIR__ . '/wp-load.php';
$ok = did_action('elementor/loaded') ? IFA_Builder::build_all() : false;
echo $ok ? "pages built\n" : "BUILD FAILED\n";
PHPEOF
  (cd "$DOCROOT" && "$PHP" ifa-build.php) || die "Page build failed."
  rm -f "$DOCROOT/ifa-build.php"
fi

say "Done!"
echo "  Site:      $BASE_URL"
echo "  Admin:     $BASE_URL/wp-admin  ($ADMIN_USER / $ADMIN_PASS)"
echo "  Editor:    $BASE_URL/wp-admin -> Pages -> edit any page with Elementor"
echo "  Settings:  $BASE_URL/wp-admin -> Settings -> Inner First Aid (Stripe links, analytics)"
echo "  Leads:     $BASE_URL/wp-admin -> IFA Leads"
echo
echo "Remaining steps: paste your Stripe payment links and GA/Pixel IDs in"
echo "Settings > Inner First Aid, then reload the site — buttons activate automatically."
