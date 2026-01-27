#!/bin/sh
set -eu

APP_NAME="zid-ui"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUNDLE_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

WWW_ROOT="/usr/local/www/zid-ui"
DB_DIR="/var/db/zid-ui"
LOG_FILE="/var/log/zid-ui.log"
ETC_DIR="/usr/local/etc/zid-ui"
NGINX_DIR="${ETC_DIR}/nginx"
NGINX_CONF="${NGINX_DIR}/zid-ui.conf"
RC_SCRIPT="/usr/local/etc/rc.d/zid-ui"
SUDOERS_FILE="/usr/local/etc/sudoers.d/zid-ui"
UPDATE_SH_SRC="${BUNDLE_ROOT}/etc/zid-ui/update.sh"
SERVICE_SH_SRC="${BUNDLE_ROOT}/etc/zid-ui/service.sh"
CONFIG_SRC="${BUNDLE_ROOT}/etc/zid-ui/zid-ui.conf"
WWW_SRC="${BUNDLE_ROOT}/www/zid-ui"
ENSURE_SRC="${BUNDLE_ROOT}/sbin/zid-ui-ensure-include"
RC_SRC="${BUNDLE_ROOT}/etc/rc.d/zid-ui"
PORT_DEFAULT="8444"

echo "[+] Installing ${APP_NAME} from ${BUNDLE_ROOT}..."

if [ ! -d "${WWW_SRC}" ]; then
  echo "[-] Diretorio ${WWW_SRC} nao encontrado. Execute este install.sh dentro do bundle extraido." 
  exit 1
fi

mkdir -p "${WWW_ROOT}" "${DB_DIR}/cache" "${DB_DIR}/locks" "${DB_DIR}/backups" "${NGINX_DIR}" "${ETC_DIR}"

touch "${LOG_FILE}"
chmod 600 "${LOG_FILE}"

# Copiar UI
cp -R "${WWW_SRC}" /usr/local/www/

# Copiar binarios e rc
if [ -f "${ENSURE_SRC}" ]; then
  cp "${ENSURE_SRC}" /usr/local/sbin/zid-ui-ensure-include
  chmod 755 /usr/local/sbin/zid-ui-ensure-include
fi

if [ -f "${RC_SRC}" ]; then
  cp "${RC_SRC}" /usr/local/etc/rc.d/zid-ui
  chmod 755 /usr/local/etc/rc.d/zid-ui
fi

# Config
if [ ! -f "${ETC_DIR}/zid-ui.conf" ]; then
  if [ -f "${CONFIG_SRC}" ]; then
    cp "${CONFIG_SRC}" "${ETC_DIR}/zid-ui.conf"
  else
    cat > "${ETC_DIR}/zid-ui.conf" <<EOF_CONF
# zid-ui config
port=${PORT_DEFAULT}
tiles_url=https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
refresh_default_seconds=5
enable_sse=false
allow_from_lan_only=true
update_url=https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz
EOF_CONF
  fi
  chmod 600 "${ETC_DIR}/zid-ui.conf"
fi

# update.sh
if [ -f "${UPDATE_SH_SRC}" ]; then
  cp "${UPDATE_SH_SRC}" "${ETC_DIR}/update.sh"
  chmod 700 "${ETC_DIR}/update.sh"
fi

if [ -f "${SERVICE_SH_SRC}" ]; then
  cp "${SERVICE_SH_SRC}" "${ETC_DIR}/service.sh"
  chmod 700 "${ETC_DIR}/service.sh"
fi

if [ ! -f "${NGINX_CONF}" ]; then
cat > "${NGINX_CONF}" <<'EOF_NGINX'
# zid-ui nginx include
# IMPORTANT: Codex must generate a full server { } using the discovered fastcgi_pass from webConfigurator.
# This file must be included by nginx config (ensure-include mechanism).
EOF_NGINX
fi

if [ ! -f "${SUDOERS_FILE}" ]; then
  mkdir -p /usr/local/etc/sudoers.d
  cat > "${SUDOERS_FILE}" <<EOF_SUDO
www ALL=(root) NOPASSWD: /usr/local/etc/zid-ui/update.sh
www ALL=(root) NOPASSWD: /usr/local/etc/zid-ui/service.sh
EOF_SUDO
  chmod 440 "${SUDOERS_FILE}"
fi

if [ ! -f "${DB_DIR}/zid-ui.db" ]; then
  touch "${DB_DIR}/zid-ui.db"
  chmod 600 "${DB_DIR}/zid-ui.db"
fi

if [ ! -f /etc/rc.conf.local ]; then
  touch /etc/rc.conf.local
fi
if ! grep -q '^zid_ui_enable=' /etc/rc.conf.local; then
  echo 'zid_ui_enable="YES"' >> /etc/rc.conf.local
fi

service zid-ui start >/dev/null 2>&1 || true

echo "[+] Done."
echo "    UI:     ${WWW_ROOT}"
echo "    Config: ${ETC_DIR}/zid-ui.conf"
echo "    Update: ${ETC_DIR}/update.sh"
