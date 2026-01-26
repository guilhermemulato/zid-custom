#!/bin/sh
set -e

SRC_BASE="/conf/zid-ui"
SRC_CSS="${SRC_BASE}/css/zid-canvas.css"
SRC_ASSETS="${SRC_BASE}/assets"

DEST_CSS="/usr/local/www/css"
DEST_ASSETS="/usr/local/www/zid-assets"

mkdir -p "${DEST_CSS}" "${DEST_ASSETS}"

if [ -f "${SRC_CSS}" ]; then
  cp -f "${SRC_CSS}" "${DEST_CSS}/zid-canvas.css"
fi

if [ -d "${SRC_ASSETS}" ]; then
  cp -R "${SRC_ASSETS}/." "${DEST_ASSETS}/"
fi
