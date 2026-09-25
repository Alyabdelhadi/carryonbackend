#!/usr/bin/env bash
# Deploys ~/repos/carryonbackend into the live admin folder.
# Run on the server after backup.sh:
#   cd ~/repos/carryonbackend && git pull && bash deploy/deploy.sh
set -euo pipefail

REPO="$(cd "$(dirname "$0")/.." && pwd)"
LIVE="${LIVE:-$HOME/public_html/admin}"
PHP="${PHP:-php}"

# Refuse to run without a backup taken in the last 2 hours.
if [ -z "$(find "$HOME" -maxdepth 1 -name 'carryon_db_*.sql' -size +1k -mmin -120)" ]; then
  echo "No fresh backup in ~ (carryon_db_*.sql). Run deploy/backup.sh first." >&2
  exit 1
fi

"$PHP" -r 'exit(PHP_VERSION_ID >= 80100 ? 0 : 1);' || {
  echo "$PHP is older than 8.1; rerun with PHP=ea-php82 (or the right binary)." >&2
  exit 1
}

# --- .env: non-secret values only -------------------------------------
ENV="$LIVE/.env"
cp "$ENV" "$HOME/carryon_env_$(date +%Y%m%d_%H%M).bak"
chmod 600 "$HOME"/carryon_env_*.bak

set_env() { # key value: replace the line, or append it when missing
  if grep -q "^$1=" "$ENV"; then
    sed -i "s|^$1=.*|$1=$2|" "$ENV"
  else
    printf '%s=%s\n' "$1" "$2" >> "$ENV"
  fi
}
add_env() { # key value: append only when missing (never overwrite)
  grep -q "^$1=" "$ENV" || printf '%s=%s\n' "$1" "$2" >> "$ENV"
}

set_env APP_DEBUG false
set_env LOG_LEVEL warning
set_env SESSION_SECURE_COOKIE true
# The old Ionic app sends no tokens; keep it working until it is retired.
set_env API_LEGACY_USER_ID_AUTH true
add_env SHUFTI_CLIENT_ID ""
add_env SHUFTI_SECRET_KEY ""
add_env SHUFTI_CALLBACK_URL ""

# --- code ---------------------------------------------------------------
rsync -rlt \
  --exclude=.git --exclude=.env --exclude='.env.*' --exclude=storage \
  --exclude=upload --exclude=public --exclude=vendor --exclude=node_modules \
  --exclude=bootstrap/cache --exclude=local-dev --exclude=tests \
  --exclude=deploy --exclude='*.log' \
  "$REPO/" "$LIVE/"

rm -f "$LIVE/test-mail.php"

# --- database -----------------------------------------------------------
cd "$LIVE"
for m in \
  2026_09_26_000000_add_identity_verification \
  2026_09_26_000100_add_password_reset_otp \
  2026_09_26_000200_create_app_refresh_tokens_table \
  2026_09_26_000300_weights_for_app \
  2026_09_26_000400_tips_as_app_rewards; do
  "$PHP" artisan migrate --path="database/migrations/$m.php" --force
done

"$PHP" artisan app-users:hash-passwords
"$PHP" artisan optimize:clear

echo
echo "DEPLOY OK"
if [ -z "$(grep -m1 '^SHUFTI_SECRET_KEY=' "$ENV" | cut -d= -f2-)" ]; then
  echo "WARNING: SHUFTI_CLIENT_ID / SHUFTI_SECRET_KEY are empty in $ENV."
  echo "Signups are refused while Shufti is enabled; fill them in, then run:"
  echo "  $PHP artisan config:clear"
fi
