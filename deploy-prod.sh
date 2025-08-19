#!/bin/bash

# Script de Deploy para Produção - DesejoLivre
set -e

echo "🚀 Iniciando deploy de produção..."

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker e tente novamente."
    exit 1
fi

# Parar e remover containers existentes
echo "🛑 Parando containers existentes..."
docker-compose -f docker-compose.prod.yml down --remove-orphans

# Remover imagens antigas (opcional)
echo "🧹 Limpando imagens antigas..."
docker system prune -f

# Build das novas imagens
echo "🔨 Fazendo build das imagens de produção..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Iniciar serviços
echo "🚀 Iniciando serviços..."
docker-compose -f docker-compose.prod.yml up -d

# Aguardar serviços ficarem prontos
echo "⏳ Aguardando serviços ficarem prontos..."
sleep 30

# Verificar status dos serviços
echo "🔍 Verificando status dos serviços..."
docker-compose -f docker-compose.prod.yml ps

# Executar migrações (se necessário)
echo "📊 Executando migrações..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Limpar cache
echo "🧹 Limpando cache..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Verificar health checks
echo "🏥 Verificando health checks..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan about

echo "✅ Deploy concluído com sucesso!"
echo "🌐 Aplicação rodando em: http://localhost"
echo "📊 Para ver logs: docker-compose -f docker-compose.prod.yml logs -f"
