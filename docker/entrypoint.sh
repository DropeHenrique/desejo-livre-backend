#!/usr/bin/env bash
set -e

cd /var/www

# 1) .env
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  else
    touch .env
  fi
fi

# 2) Injetar variáveis de ambiente do container no .env se ainda não existirem
# (opcional – útil no primeiro deploy)
grep -q '^APP_ENV=' .env || echo "APP_ENV=${APP_ENV:-local}" >> .env
grep -q '^APP_DEBUG=' .env || echo "APP_DEBUG=${APP_DEBUG:-true}" >> .env

# 3) Composer
if [ "${APP_ENV}" = "production" ]; then
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
else
  composer install --prefer-dist --no-interaction
fi

# 4) APP_KEY (gera só se vazio)
if ! grep -q '^APP_KEY=' .env || [ -z "$(grep '^APP_KEY=' .env | cut -d= -f2)" ]; then
  php artisan key:generate --force || true
fi

# 5) Bootstrap
php artisan storage:link || true
php artisan migrate --force || true

# Em dev dá pra pular os caches; em prod mantenha
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
