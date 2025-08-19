#!/bin/sh
set -e

echo "[Entrypoint] Iniciando PHP-FPM e mantendo container vivo..."

# Inicia PHP-FPM no foreground
php-fpm -F &

# Aguarda qualquer processo filho morrer (evita container sair)
# e mantém o container vivo
tail -f /dev/null
