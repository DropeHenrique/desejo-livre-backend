#!/bin/bash

# Script para rodar todos os seeders de desenvolvimento
# Uso: ./scripts/run-dev-seeders.sh

echo "🌱 Iniciando seeders de desenvolvimento..."

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

echo "📊 Executando seeders..."

# 1. Seeders básicos (estados, cidades, etc.)
echo "📍 Executando seeders de localização..."
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=LocationSeeder

# 2. Seeders de planos e serviços
echo "💳 Executando seeders de planos e serviços..."
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=ServiceTypeSeeder
php artisan db:seed --class=CompanionDistrictSeeder

# 3. Seeders de usuários de teste
echo "👥 Executando seeders de usuários..."
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=TestUsersSeeder

# 4. Seeders de perfis e dados relacionados
echo "🔄 Executando seeders de perfis..."
php artisan db:seed --class=CompanionProfileSeeder
php artisan db:seed --class=CompanionServiceSeeder
php artisan db:seed --class=TransvestiteMaleEscortSeeder

# 5. Seeders de conteúdo
echo "📝 Executando seeders de conteúdo..."
php artisan db:seed --class=BlogSeeder
php artisan db:seed --class=MediaSeeder

# 6. Seeders de assinaturas de teste
echo "💎 Executando seeders de assinaturas..."
php artisan db:seed --class=TestSubscriptionSeeder

# 7. Seeder de dados de exemplo
echo "🎯 Executando seeder de dados de exemplo..."
php artisan db:seed --class=SampleDataSeeder

echo "✅ Todos os seeders de desenvolvimento foram executados com sucesso!"
echo ""
echo "📋 Resumo dos seeders executados:"
echo "   • Estados e cidades"
echo "   • Planos e serviços"
echo "   • Usuários de teste (admin, clientes, acompanhantes)"
echo "   • Perfis de acompanhantes"
echo "   • Conteúdo de blog e mídia"
echo "   • Assinaturas de teste"
echo "   • Dados de exemplo gerais"
echo ""
echo "🚀 O sistema está pronto para desenvolvimento!"
