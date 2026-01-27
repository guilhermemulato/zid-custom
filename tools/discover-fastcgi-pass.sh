#!/bin/sh
set -eu

log() {
  echo "[discover-fastcgi-pass] $*" >&2
}

CANDIDATES="/var/etc/nginx-webConfigurator.conf /var/etc/nginx-webConfigurator.conf* /var/etc/nginx/*.conf /var/etc/nginx*.conf"

FOUND=""
for f in $CANDIDATES; do
  if [ -f "$f" ]; then
    line=$(awk '/fastcgi_pass/{print; exit}' "$f" 2>/dev/null || true)
    if [ -n "$line" ]; then
      FOUND="$line"
      log "found in $f"
      break
    fi
  fi
done

if [ -z "$FOUND" ]; then
  log "nenhum fastcgi_pass encontrado"
  exit 1
fi

echo "$FOUND" | sed -E 's/^[[:space:]]*fastcgi_pass[[:space:]]+//; s/;[[:space:]]*$//'
