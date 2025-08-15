#!/bin/bash

# Script para rodar seeders essenciais para produção
# Uso: ./scripts/run-production-seeders.sh

echo "🚀 Iniciando seeders de produção..."

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script do diretório raiz do projeto Laravel"
    exit 1
fi

# Verificar se o banco está acessível
echo "🔍 Verificando conexão com o banco..."
php artisan tinker --execute="DB::connection()->getPdo(); echo '✅ Conexão com banco OK';" 2>/dev/null || {
    echo "❌ Erro: Não foi possível conectar ao banco de dados"
    echo "Verifique se o banco está rodando e as configurações estão corretas"
    exit 1
}

echo "📊 Executando seeders essenciais para produção..."

# 1. Seeders básicos ESSENCIAIS
echo "📍 Executando seeders de localização..."
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=LocationSeeder

# 2. Seeders de planos e serviços ESSENCIAIS
echo "💳 Executando seeders de planos e serviços..."
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=ServiceTypeSeeder
php artisan db:seed --class=CompanionDistrictSeeder

# 3. Seeder de usuário admin ESSENCIAL
echo "👑 Executando seeder de usuário admin..."
php artisan db:seed --class=AdminUserSeeder

echo "✅ Seeders de produção executados com sucesso!"
echo ""
echo "📋 Dados inseridos (essenciais para produção):"
echo "   • Estados brasileiros"
echo "   • Cidades principais"
echo "   • Planos de assinatura"
echo "   • Tipos de serviços"
echo "   • Distritos de acompanhantes"
echo "   • Usuário administrador"
echo ""
echo "⚠️  NOTA: Este script NÃO executa seeders de dados de teste"
echo "   Para dados de teste, use: ./scripts/run-dev-seeders.sh"
echo ""
echo "🚀 O sistema está pronto para produção!"
