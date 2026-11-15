#!/usr/bin/env bash
set -euo pipefail

SERVICE_NAME="mysql"
LOGGER_TAG="mysql-watchdog"
MYSQLADMIN_BIN="/usr/bin/mysqladmin"

log() {
  /usr/bin/logger -t "$LOGGER_TAG" "$1"
}

mysql_responding() {
  if ! /usr/bin/systemctl is-active --quiet "$SERVICE_NAME"; then
    return 1
  fi

  "$MYSQLADMIN_BIN" ping --silent >/dev/null 2>&1
}

if mysql_responding; then
  log "MySQL healthy. No action required."
  exit 0
fi

log "MySQL unhealthy or stopped. Attempting automatic recovery."

/usr/bin/systemctl reset-failed "$SERVICE_NAME" >/dev/null 2>&1 || true
/usr/bin/systemctl start "$SERVICE_NAME"

sleep 5

if mysql_responding; then
  log "MySQL recovered successfully after automatic restart."
  exit 0
fi

log "MySQL recovery failed. Manual review required."
exit 1
