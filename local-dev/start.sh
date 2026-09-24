#!/bin/bash
# Runs the CarryOn backend locally with a portable MySQL and static PHP,
# using .env.local (no real mail, no real push). Nothing is installed
# system-wide; everything lives under ~/development/carryon-local.
#
#   local-dev/start.sh            # start MySQL (importing the dump on first run) + PHP server
#   local-dev/start.sh stop       # stop both
#
# API:    http://127.0.0.1:8000/api
# Admin:  http://127.0.0.1:8000/login
# Uploads http://127.0.0.1:8000/upload/<kind>/<file>
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TOOLS="$HOME/development/carryon-local"
PHP="$TOOLS/php/php"
MYSQL_HOME="$(ls -d "$TOOLS"/mysql-8.* 2>/dev/null | head -1)"
DATA="$TOOLS/data"
SOCK="$TOOLS/mysql.sock"
PORT_DB=3306
PORT_WEB=8000
DUMP="$ROOT/../carryon_carryon.sql"
DB=carryon_carryon

mysql_cmd() { "$MYSQL_HOME/bin/mysql" -uroot --socket="$SOCK" "$@"; }

stop() {
  pkill -f "\-S 127.0.0.1:$PORT_WEB" 2>/dev/null || true
  if [ -S "$SOCK" ]; then "$MYSQL_HOME/bin/mysqladmin" -uroot --socket="$SOCK" shutdown || true; fi
  echo "stopped"
}

if [ "${1:-}" = "stop" ]; then stop; exit 0; fi

[ -x "$PHP" ] || { echo "static PHP missing at $PHP"; exit 1; }
[ -n "$MYSQL_HOME" ] || { echo "MySQL tarball not unpacked under $TOOLS"; exit 1; }

if [ ! -d "$DATA/mysql" ]; then
  echo "Initialising MySQL data dir..."
  "$MYSQL_HOME/bin/mysqld" --initialize-insecure --datadir="$DATA" --basedir="$MYSQL_HOME" >/dev/null
fi

if [ ! -S "$SOCK" ]; then
  echo "Starting MySQL on port $PORT_DB..."
  nohup "$MYSQL_HOME/bin/mysqld" --datadir="$DATA" --basedir="$MYSQL_HOME" \
    --socket="$SOCK" --port=$PORT_DB --bind-address=127.0.0.1 \
    --mysqlx=OFF --sql-mode="" --log-error="$TOOLS/mysql.err" >/dev/null 2>&1 &
  for i in $(seq 1 60); do [ -S "$SOCK" ] && break; sleep 1; done
  [ -S "$SOCK" ] || { echo "MySQL did not start; see $TOOLS/mysql.err"; exit 1; }
fi

if ! mysql_cmd -e "USE $DB" 2>/dev/null; then
  echo "Importing $DUMP (first run)..."
  mysql_cmd -e "CREATE DATABASE $DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
  mysql_cmd "$DB" < "$DUMP"
  echo "Imported: $(mysql_cmd -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB'") tables"
fi

cd "$ROOT"
mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
pkill -f "\-S 127.0.0.1:$PORT_WEB" 2>/dev/null || true
echo "Starting PHP server on http://127.0.0.1:$PORT_WEB ..."
# PHP 8.4 deprecation notices from old vendor code must not leak into JSON.
APP_ENV=local nohup "$PHP" -d "error_reporting=E_ALL&~E_DEPRECATED" -d display_errors=0 -S 127.0.0.1:$PORT_WEB local-dev/router.php > "$TOOLS/php-server.log" 2>&1 &
sleep 1
echo "API check: $(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:$PORT_WEB/api/services)"
