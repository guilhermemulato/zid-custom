#!/bin/sh
set -e

URL="https://s3.soulsolucoes.com.br/soul/portal/zid-cavas-latest.tar.gz"
BASE="/conf/zid-ui"
TMP="$(mktemp -d)"
ARCHIVE="${TMP}/zid-cavas-latest.tar.gz"
STATUS_OK=0

cleanup() {
  rm -rf "${TMP}"
}
trap cleanup EXIT

printf "%s\\n" "Baixando bundle do S3..."
if fetch -o "${ARCHIVE}" "${URL}" 2>/dev/null; then
  STATUS_OK=1
else
  if curl -L -o "${ARCHIVE}" "${URL}"; then
    STATUS_OK=1
  fi
fi

if [ "${STATUS_OK}" -ne 1 ]; then
  printf "%s\\n" "Falha ao baixar o bundle do S3." >&2
  exit 1
fi

if [ ! -s "${ARCHIVE}" ]; then
  printf "%s\\n" "Bundle baixado, mas o arquivo esta vazio." >&2
  exit 1
fi

printf "%s\\n" "Download concluido."

tar -xzf "${ARCHIVE}" -C "${TMP}"

if [ -d "${TMP}/zid-ui" ]; then
  SRC="${TMP}/zid-ui"
else
  SRC="${TMP}"
fi

mkdir -p "${BASE}/css" "${BASE}/assets"

if [ -f "${SRC}/css/zid-canvas.css" ]; then
  cp -f "${SRC}/css/zid-canvas.css" "${BASE}/css/zid-canvas.css"
fi

if [ -d "${SRC}/assets" ]; then
  cp -R "${SRC}/assets/." "${BASE}/assets/"
fi

if [ -f "${SRC}/apply.sh" ]; then
  cp -f "${SRC}/apply.sh" "${BASE}/apply.sh"
fi

if [ -f "${SRC}/update.sh" ]; then
  cp -f "${SRC}/update.sh" "${BASE}/update.sh"
fi

if sh "${BASE}/apply.sh"; then
  printf "%s\\n" "Update aplicado com sucesso."
else
  printf "%s\\n" "Falha ao aplicar o update." >&2
  exit 1
fi
