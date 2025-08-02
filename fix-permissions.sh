#!/bin/bash

# Script para corrigir permissões do Laravel
echo "🔧 Corrigindo permissões do Laravel..."

# Definir o usuário do servidor web (www-data para Apache/Nginx)
WEB_USER="www-data"
WEB_GROUP="www-data"

# Criar estrutura completa de diretórios se não existir
echo "📁 Criando estrutura de diretórios..."
sudo mkdir -p storage/framework/{cache/data,sessions,testing,views}
sudo mkdir -p storage/app/{public,temp}
sudo mkdir -p storage/logs
sudo mkdir -p bootstrap/cache

# Corrigir ownership dos diretórios críticos
echo "📁 Corrigindo ownership..."
sudo chown -R $WEB_USER:$WEB_GROUP storage/
sudo chown -R $WEB_USER:$WEB_GROUP bootstrap/cache/

# Definir permissões corretas
echo "🔐 Definindo permissões..."
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/

# Criar diretório de logs se não existir
echo "📝 Verificando diretório de logs..."
sudo mkdir -p storage/logs
sudo touch storage/logs/laravel.log
sudo chown -R $WEB_USER:$WEB_GROUP storage/logs/
sudo chmod -R 775 storage/logs/

# Permissões para arquivos específicos
echo "📄 Ajustando arquivos específicos..."
sudo chmod 664 .env 2>/dev/null || echo "Arquivo .env não encontrado"

# Limpar caches após correção de permissões
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✅ Permissões corrigidas com sucesso!"
echo ""
echo "📋 Resumo das permissões aplicadas:"
echo "   - storage/: $WEB_USER:$WEB_GROUP (775)"
echo "   - bootstrap/cache/: $WEB_USER:$WEB_GROUP (775)"
echo "   - storage/logs/: $WEB_USER:$WEB_GROUP (775)"
echo "   - storage/framework/: $WEB_USER:$WEB_GROUP (775)"
echo ""
echo "📁 Estrutura de diretórios criada:"
echo "   - storage/framework/cache/data/"
echo "   - storage/framework/sessions/"
echo "   - storage/framework/testing/"
echo "   - storage/framework/views/"
echo "   - storage/app/public/"
echo "   - storage/app/temp/"
echo ""
echo "Para usar este script novamente: bash fix-permissions.sh"
