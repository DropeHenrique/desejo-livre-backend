#!/bin/sh
set -e
cd /code

echo "[Entrypoint] Laravel em /code"

# Garantir permissões mínimas
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# .env se faltar
[ -f .env ] || { [ -f .env.example ] && cp .env.example .env || true; }

# Instala vendor (se ainda não tiver)
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Front (se existir)
if [ -f package.json ]; then
  [ -d node_modules ] || (npm ci || npm install)
  npm run build || true
fi

# Tarefas Laravel (não falham o container se DB ainda não estiver pronto)
php artisan key:generate --force || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true
php artisan view:clear || true

echo "[Entrypoint] php: $(php -v | head -n1)"
echo "[Entrypoint] node: $(node -v), npm: $(npm -v)"

exec php-fpm -F
