#!/bin/sh
set -e

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
BUNDLE_DIR="${SCRIPT_DIR}"

if [ -n "$1" ]; then
  BUNDLE_DIR="$1"
fi

if [ ! -f "${BUNDLE_DIR}/css/zid-canvas.css" ]; then
  echo "Arquivo nao encontrado: ${BUNDLE_DIR}/css/zid-canvas.css" >&2
  exit 1
fi

mkdir -p /conf/zid-ui/css /conf/zid-ui/assets

cp -f "${BUNDLE_DIR}/css/zid-canvas.css" /conf/zid-ui/css/zid-canvas.css
cp -R "${BUNDLE_DIR}/assets/." /conf/zid-ui/assets/
cp -f "${BUNDLE_DIR}/apply.sh" /conf/zid-ui/apply.sh
cp -f "${BUNDLE_DIR}/update.sh" /conf/zid-ui/update.sh

chmod +x /conf/zid-ui/apply.sh /conf/zid-ui/update.sh

sh /conf/zid-ui/apply.sh

printf "%s\n" "Setup concluido. Aplique o tema em System -> General Setup -> Theme (zid-canvas)."
