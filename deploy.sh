#!/bin/bash

# Script de Deploy para Produção - DesejoLivre
# Uso: ./deploy.sh

set -e

echo "🚀 Iniciando deploy do DesejoLivre..."

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Por favor, inicie o Docker."
    exit 1
fi

# Verificar se o arquivo .env.production existe
if [ ! -f .env.production ]; then
    echo "❌ Arquivo .env.production não encontrado."
    echo "📝 Copie o arquivo env.production.example para .env.production e configure as variáveis."
    exit 1
fi

# Backup do banco de dados (se existir)
if docker ps -q -f name=desejo-livre-db-prod > /dev/null 2>&1; then
    echo "💾 Fazendo backup do banco de dados..."
    docker exec desejo-livre-db-prod pg_dump -U desejo-livre_user desejolivre_prod > backup_$(date +%Y%m%d_%H%M%S).sql
fi

# Parar containers existentes
echo "🛑 Parando containers existentes..."
docker-compose -f docker-compose.prod.yml down

# Remover imagens antigas
echo "🧹 Removendo imagens antigas..."
docker system prune -f

# Construir novas imagens
echo "🔨 Construindo novas imagens..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Iniciar serviços
echo "🚀 Iniciando serviços..."
docker-compose -f docker-compose.prod.yml up -d

# Aguardar serviços ficarem prontos
echo "⏳ Aguardando serviços ficarem prontos..."
sleep 30

# Executar migrações
echo "🗄️ Executando migrações..."
docker exec desejo-livre-app-prod php artisan migrate --force

# Limpar cache
echo "🧹 Limpando cache..."
docker exec desejo-livre-app-prod php artisan config:clear
docker exec desejo-livre-app-prod php artisan cache:clear
docker exec desejo-livre-app-prod php artisan route:clear
docker exec desejo-livre-app-prod php artisan view:clear

# Otimizar aplicação
echo "⚡ Otimizando aplicação..."
docker exec desejo-livre-app-prod php artisan config:cache
docker exec desejo-livre-app-prod php artisan route:cache
docker exec desejo-livre-app-prod php artisan view:cache

# Verificar status dos serviços
echo "🔍 Verificando status dos serviços..."
docker-compose -f docker-compose.prod.yml ps

# Testar API
echo "🧪 Testando API..."
if curl -f http://localhost/api/ping > /dev/null 2>&1; then
    echo "✅ API está funcionando!"
else
    echo "⚠️ API pode não estar respondendo ainda. Aguarde alguns minutos."
fi

echo "🎉 Deploy concluído com sucesso!"
echo "📊 Para verificar logs: docker-compose -f docker-compose.prod.yml logs -f"
echo "🌐 Acesse: https://api.desejolivre.com"
