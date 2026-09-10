#!/bin/sh
set -e

mkdir -p /app/var/cache /app/var/log /app/var/data /app/vendor

if [ "$(id -u)" = "0" ]; then
    chown -R app:app /app/var /app/vendor 2>/dev/null || true
    if [ "$1" = "php-fpm" ]; then
        exec "$@"
    fi
    exec runuser -u app -- "$@"
fi

exec "$@"
