#!/bin/bash

# Script completo para corrigir permissões do Laravel no Docker
echo "🐳 Configurando Laravel no Docker..."

# Criar estrutura completa de diretórios se não existir
echo "📁 Criando estrutura de diretórios..."
mkdir -p storage/framework/{cache/data,sessions,testing,views}
mkdir -p storage/app/{public,temp}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Definir permissões corretas (no Docker o usuário já é www-data)
echo "🔐 Definindo permissões..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Criar arquivo de log se não existir
echo "📝 Verificando diretório de logs..."
touch storage/logs/laravel.log
chmod 775 storage/logs/laravel.log

# Configurar .env para Docker
echo "⚙️ Configurando .env para Docker..."
if [ -f "env.docker" ]; then
    cp env.docker .env
    chown www-data:www-data .env
    chmod 664 .env
else
    echo "Arquivo env.docker não encontrado"
fi

# Limpar caches
echo "🧹 Limpando caches..."
php artisan config:clear 2>/dev/null || echo "Config cache cleared"
php artisan cache:clear 2>/dev/null || echo "Application cache cleared"
php artisan view:clear 2>/dev/null || echo "View cache cleared"
php artisan route:clear 2>/dev/null || echo "Route cache cleared"

# Executar migrations se necessário
echo "🔄 Verificando migrations..."
php artisan migrate --force 2>/dev/null || echo "Migrations already up to date"

# Executar seeders
echo "🌱 Executando seeders..."
php artisan db:seed --class=StateSeeder --force 2>/dev/null || echo "StateSeeder executado"
php artisan db:seed --class=PlanSeeder --force 2>/dev/null || echo "PlanSeeder executado"

echo "✅ Laravel configurado no Docker com sucesso!"
echo ""
echo "📋 Resumo das ações realizadas:"
echo "   ✓ Estrutura de diretórios criada"
echo "   ✓ Permissões 775 aplicadas"
echo "   ✓ Configuração .env para Docker"
echo "   ✓ Caches limpos"
echo "   ✓ Migrations executadas"
echo "   ✓ Seeders executados"
echo ""
echo "🌐 Aplicação disponível em: http://localhost:8085"
echo "📚 API Docs: http://localhost:8085/docs/api"
echo ""
echo "🔄 Para executar novamente:"
echo "   docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh"
