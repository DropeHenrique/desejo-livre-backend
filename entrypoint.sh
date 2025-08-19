#!/bin/sh
set -e

echo "[Entrypoint] Preparando app Laravel..."

# Garante permissões (útil quando o repositório é clonado pelo painel)
chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

# Se não existir .env, cria a partir do exemplo (opcional)
if [ ! -f "/var/www/.env" ] && [ -f "/var/www/.env.example" ]; then
  cp /var/www/.env.example /var/www/.env
fi

# Instala dependências PHP (sem interagir)
composer install --no-interaction --prefer-dist --optimize-autoloader

# Instala e builda assets (Node 20 já instalado na imagem)
if [ -f "package.json" ]; then
  npm ci || npm install
  npm run build || true
fi

# Gera key se faltar
php artisan key:generate --force || true

# Link de storage
php artisan storage:link || true

# Migrações (se DB estiver acessível)
php artisan migrate --force || true

# Limpa caches
php artisan config:clear || true
php artisan route:clear || true
php artisan cache:clear || true
php artisan view:clear || true

echo "[Entrypoint] Iniciando PHP-FPM (foreground)..."
exec php-fpm -F
