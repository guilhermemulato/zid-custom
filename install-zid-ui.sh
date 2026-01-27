#!/bin/sh
set -eu

if [ $# -lt 1 ]; then
  echo "Uso: $0 <IP-PFSENSE>"
  exit 1
fi

IP="$1"
BUNDLE="zid-cavas-latest.tar.gz"
REMOTE_TMP="/tmp"
REMOTE_DIR="/tmp/zid-ui-install"

if [ ! -f "$BUNDLE" ]; then
  echo "Arquivo $BUNDLE nao encontrado no diretorio atual."
  exit 1
fi

echo "[+] Enviando bundle para $IP..."
scp "$BUNDLE" "root@${IP}:${REMOTE_TMP}/"

echo "[+] Instalando no pfSense..."
ssh "root@${IP}" <<'EOSSH'
set -eu

BUNDLE="/tmp/zid-cavas-latest.tar.gz"
REMOTE_DIR="/tmp/zid-ui-install"

mkdir -p "$REMOTE_DIR"
cd "$REMOTE_DIR"
tar -xzf "$BUNDLE"

cp -R zid-ui/www/zid-ui /usr/local/www/
cp -R zid-ui/etc/zid-ui /usr/local/etc/
cp -R zid-ui/etc/rc.d/zid-ui /usr/local/etc/rc.d/
cp -R zid-ui/sbin/zid-ui-ensure-include /usr/local/sbin/

chmod +x /usr/local/etc/zid-ui/install.sh
/usr/local/etc/zid-ui/install.sh

echo "[+] ZID UI instalado. Acesse: https://${HOSTNAME:-pfsense}:8444"
EOSSH
