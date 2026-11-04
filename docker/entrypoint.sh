#!/bin/sh
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY precisa estar configurada no arquivo .env." >&2
    exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
composer install --no-interaction --prefer-dist
php artisan config:clear --no-interaction
php artisan migrate --force --no-interaction

exec "$@"
