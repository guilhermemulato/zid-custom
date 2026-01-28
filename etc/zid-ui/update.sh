#!/bin/sh
set -eu

APP_NAME="zid-ui"
WWW_ROOT="/usr/local/www/zid-ui"
ETC_DIR="/usr/local/etc/zid-ui"
CONF_FILE="${ETC_DIR}/zid-ui.conf"
LOG_FILE="/var/log/zid-ui.log"
LOCK_FILE="/var/run/zid-ui-update.lock"

cfg_get() {
  key="$1"
  awk -F= -v k="$key" 'BEGIN{v=""} $0 ~ "^[[:space:]]*"k"=" {sub("^[^=]*=",""); gsub("\r",""); v=$0} END{print v}' "${CONF_FILE}" 2>/dev/null || true
}

URL="$(cfg_get update_url)"
[ -n "${URL}" ] || URL="https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz"

TMP_DIR="/tmp/${APP_NAME}-update.$$"
ARCHIVE="${TMP_DIR}/pkg.tar.gz"
STAGE="${TMP_DIR}/stage"
BACKUP_BASE="/var/db/zid-ui/backups"
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="${BACKUP_BASE}/${TS}"

mkdir -p "${BACKUP_BASE}"

log() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${LOG_FILE}"
}

cleanup() {
  rm -rf "${TMP_DIR}" 2>/dev/null || true
  rm -f "${LOCK_FILE}" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

if [ -e "${LOCK_FILE}" ]; then
  log "Update already running (lock exists)."
  echo "Update already running."
  exit 1
fi
touch "${LOCK_FILE}"
chmod 600 "${LOCK_FILE}"

log "Starting update from ${URL}"

mkdir -p "${TMP_DIR}" "${STAGE}"

fetch -q -o "${ARCHIVE}" "${URL}"

if [ ! -s "${ARCHIVE}" ]; then
  log "ERROR: downloaded archive is empty"
  exit 1
fi

tar -xzf "${ARCHIVE}" -C "${STAGE}"

NEW_ROOT=""
if [ -d "${STAGE}/www/zid-ui" ]; then
  NEW_ROOT="${STAGE}/www/zid-ui"
elif [ -d "${STAGE}/zid-ui" ]; then
  NEW_ROOT="${STAGE}/zid-ui"
else
  log "ERROR: archive missing expected root folder (www/zid-ui or zid-ui)"
  exit 1
fi

mkdir -p "${BACKUP_DIR}"
if [ -d "${WWW_ROOT}" ]; then
  log "Backing up ${WWW_ROOT} to ${BACKUP_DIR}/www/${APP_NAME}.tar"
  mkdir -p "${BACKUP_DIR}/www"
  (cd /usr/local/www && tar -cf "${BACKUP_DIR}/www/${APP_NAME}.tar" "${APP_NAME}") || true
fi

TMP_TARGET="/usr/local/www/.${APP_NAME}.new.${TS}"
rm -rf "${TMP_TARGET}" 2>/dev/null || true
mkdir -p "${TMP_TARGET}"

( cd "${NEW_ROOT}" && tar -cf - . ) | ( cd "${TMP_TARGET}" && tar -xpf - )

if [ ! -f "${TMP_TARGET}/index.php" ]; then
  log "ERROR: new build missing index.php"
  exit 1
fi

OLD_TARGET="/usr/local/www/.${APP_NAME}.old.${TS}"
if [ -d "${WWW_ROOT}" ]; then
  mv "${WWW_ROOT}" "${OLD_TARGET}"
fi
mv "${TMP_TARGET}" "${WWW_ROOT}"

log "Deployed new version to ${WWW_ROOT}"

service nginx onereload >/dev/null 2>&1 || \
  service nginx reload >/dev/null 2>&1 || \
  service nginx restart >/dev/null 2>&1 || true

if command -v pgrep >/dev/null 2>&1; then
  PID="$(pgrep -f "nginx: master process /usr/local/sbin/nginx -c /var/etc/nginx-webConfigurator.conf" | head -n 1)"
  if [ -n "${PID}" ]; then
    kill -HUP "${PID}" >/dev/null 2>&1 || true
  fi
fi

log "Update finished OK"
echo "OK"
