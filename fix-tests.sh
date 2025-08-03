#!/bin/bash

# Script principal para corrigir problemas dos testes
echo "=== Corrigindo problemas dos testes ==="

# 1. Corrigir permissões
echo "1. Corrigindo permissões..."
./fix-test-permissions.sh

# 2. Adicionar imports necessários
echo "2. Adicionando imports necessários..."
./fix-test-imports.sh

# 3. Limpar cache
echo "3. Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Reconstruir container se necessário
echo "4. Verificando se é necessário reconstruir o container..."
if [ "$1" = "--rebuild" ]; then
    echo "Reconstruindo container Docker..."
    docker-compose down
    docker-compose build --no-cache
    docker-compose up -d
    echo "Aguardando container inicializar..."
    sleep 10
fi

# 5. Executar migrações
echo "5. Executando migrações..."
php artisan migrate:fresh --seed

echo "=== Correções concluídas ==="
echo ""
echo "Para executar os testes:"
echo "  php artisan test"
echo ""
echo "Para executar testes específicos:"
echo "  php artisan test --filter=AuthTest"
echo "  php artisan test --filter=MediaControllerTest"
echo ""
echo "Para reconstruir o container e corrigir:"
echo "  ./fix-tests.sh --rebuild"
