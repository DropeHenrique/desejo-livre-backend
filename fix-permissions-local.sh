#!/bin/bash

# Script para corrigir permissões do Laravel em ambiente de desenvolvimento local
echo "🔧 Corrigindo permissões do Laravel (desenvolvimento local)..."

# Usar o usuário atual para desenvolvimento local
CURRENT_USER=$(whoami)
CURRENT_GROUP=$(id -gn)

echo "👤 Usuário atual: $CURRENT_USER:$CURRENT_GROUP"

# Criar estrutura completa de diretórios se não existir
echo "📁 Criando estrutura de diretórios..."
mkdir -p storage/framework/{cache/data,sessions,testing,views}
mkdir -p storage/app/{public,temp}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Corrigir ownership dos diretórios críticos
echo "📁 Corrigindo ownership..."
sudo chown -R $CURRENT_USER:$CURRENT_GROUP storage/
sudo chown -R $CURRENT_USER:$CURRENT_GROUP bootstrap/cache/

# Definir permissões corretas
echo "🔐 Definindo permissões..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Criar diretório de logs se não existir
echo "📝 Verificando diretório de logs..."
mkdir -p storage/logs
touch storage/logs/laravel.log
chmod -R 775 storage/logs/

# Permissões para arquivos específicos
echo "📄 Ajustando arquivos específicos..."
chmod 664 .env 2>/dev/null || echo "Arquivo .env não encontrado"

# Limpar caches após correção de permissões
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✅ Permissões corrigidas com sucesso!"
echo ""
echo "📋 Resumo das permissões aplicadas:"
echo "   - storage/: $CURRENT_USER:$CURRENT_GROUP (775)"
echo "   - bootstrap/cache/: $CURRENT_USER:$CURRENT_GROUP (775)"
echo "   - storage/logs/: $CURRENT_USER:$CURRENT_GROUP (775)"
echo "   - storage/framework/: $CURRENT_USER:$CURRENT_GROUP (775)"
echo ""
echo "📁 Estrutura de diretórios criada:"
echo "   - storage/framework/cache/data/"
echo "   - storage/framework/sessions/"
echo "   - storage/framework/testing/"
echo "   - storage/framework/views/"
echo "   - storage/app/public/"
echo "   - storage/app/temp/"
echo ""
echo "💡 Para produção, use: ./fix-permissions.sh"
echo "🏠 Para desenvolvimento: ./fix-permissions-local.sh"
