#!/bin/sh
set -eu

SERVICE_NAME="${1:-}"
ACTION="${2:-}"

if [ -z "${SERVICE_NAME}" ] || [ -z "${ACTION}" ]; then
  echo "Uso: service.sh <service> <action>"
  exit 1
fi

case "${ACTION}" in
  start|stop|restart|onestart|onestop|onerestart|status|onestatus) ;;
  *)
    echo "Acao invalida"
    exit 1
    ;;
 esac

exec /usr/sbin/service "${SERVICE_NAME}" "${ACTION}"
