#!/usr/bin/env bash
set -e

cd /var/www

# Composer install (honre dev/prod via APP_ENV)
if [ "${APP_ENV}" = "production" ]; then
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
else
  composer install --prefer-dist --no-interaction
fi

# Chaves/links/migrations
php artisan key:generate --force || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
