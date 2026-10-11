#!/bin/sh
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY precisa estar configurada no arquivo .env do servidor." >&2
    exit 1
fi

rm -f bootstrap/cache/config.php
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8089
