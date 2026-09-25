#!/usr/bin/env bash
# Backs up the live database and admin files into ~ before a deploy.
# Run on the server: bash ~/repos/carryonbackend/deploy/backup.sh
set -euo pipefail

LIVE="${LIVE:-$HOME/public_html/admin}"
TS=$(date +%Y%m%d_%H%M)

# Read single keys without sourcing .env (values may contain shell characters).
env_value() {
  grep -m1 "^$1=" "$LIVE/.env" | cut -d= -f2- | sed 's/^"//; s/"$//'
}

DB_OUT="$HOME/carryon_db_$TS.sql"
FILES_OUT="$HOME/carryon_admin_files_$TS.tgz"

MYSQL_PWD="$(env_value DB_PASSWORD)" mysqldump \
  -h"$(env_value DB_HOST)" -u"$(env_value DB_USERNAME)" \
  --single-transaction "$(env_value DB_DATABASE)" > "$DB_OUT"

if ! tail -1 "$DB_OUT" | grep -q 'Dump completed'; then
  echo "BACKUP FAILED: $DB_OUT is incomplete" >&2
  exit 1
fi

tar --exclude=storage/logs --exclude=vendor -czf "$FILES_OUT" \
  -C "$(dirname "$LIVE")" "$(basename "$LIVE")"

ls -lh "$DB_OUT" "$FILES_OUT"
echo "BACKUP OK"
