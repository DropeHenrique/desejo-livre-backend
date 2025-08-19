#!/bin/sh
set -e
cd /code

echo "[Entrypoint] Preparando Laravel em /code ..."

# Permissões básicas (não usar 777 global)
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Se faltar .env, cria a partir do exemplo
[ -f .env ] || [ ! -f .env.example ] || cp .env.example .env

# Se não existir vendor, instala (o script do EasyPanel também roda composer;
# deixar idempotente não faz mal)
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Node build só se existir package.json e ainda não tiver build
if [ -f package.json ]; then
  command -v npm >/dev/null 2>&1 || { echo "npm não encontrado"; exit 1; }
  [ -d node_modules ] || (npm ci || npm install)
  npm run build || true
fi

# Tarefas Laravel (não falham o container se DB não estiver pronto)
php artisan key:generate --force || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true
php artisan view:clear || true

echo "[Entrypoint] Iniciando PHP-FPM em foreground..."
exec php-fpm -F
