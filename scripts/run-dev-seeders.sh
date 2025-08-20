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

# Função para executar seeder com verificação de erro
run_seeder() {
    local seeder_name=$1
    local class_name=$2

    echo "📍 Executando $seeder_name..."
    if php artisan db:seed --class=$class_name; then
        echo "✅ $seeder_name executado com sucesso!"
    else
        echo "❌ Erro ao executar $seeder_name"
        return 1
    fi
}

# 1. Seeders básicos (estados, cidades, etc.) - ORDEM CRÍTICA
echo "📍 Executando seeders de localização..."
run_seeder "StateSeeder" "StateSeeder" || exit 1
run_seeder "LocationSeeder" "LocationSeeder" || exit 1

# 2. Seeders de planos e serviços
echo "💳 Executando seeders de planos e serviços..."
run_seeder "PlanSeeder" "PlanSeeder" || exit 1
run_seeder "ServiceTypeSeeder" "ServiceTypeSeeder" || exit 1

# 3. Seeders de usuários de teste
echo "👥 Executando seeders de usuários..."
run_seeder "AdminUserSeeder" "AdminUserSeeder" || exit 1
run_seeder "TestUsersSeeder" "TestUsersSeeder" || exit 1

# 4. Seeders de perfis e dados relacionados
echo "🔄 Executando seeders de perfis..."
run_seeder "CompanionProfileSeeder" "CompanionProfileSeeder" || exit 1
run_seeder "CompanionDistrictSeeder" "CompanionDistrictSeeder" || exit 1
run_seeder "CompanionServiceSeeder" "CompanionServiceSeeder" || exit 1
run_seeder "TransvestiteMaleEscortSeeder" "TransvestiteMaleEscortSeeder" || exit 1

# 5. Seeders de conteúdo
echo "📝 Executando seeders de conteúdo..."
run_seeder "BlogSeeder" "BlogSeeder" || exit 1
run_seeder "MediaSeeder" "MediaSeeder" || exit 1

# 6. Seeders de assinaturas de teste
echo "💎 Executando seeders de assinaturas..."
run_seeder "TestSubscriptionSeeder" "TestSubscriptionSeeder" || exit 1

# 7. Seeder de dados de exemplo
echo "🎯 Executando seeder de dados de exemplo..."
run_seeder "SampleDataSeeder" "SampleDataSeeder" || exit 1

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
