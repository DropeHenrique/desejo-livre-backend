#!/bin/bash

# Script para rodar seeders de localização (estados e cidades)
# Uso: ./scripts/run-location-seeders.sh

echo "📍 Iniciando seeders de localização..."

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

echo "🗺️ Executando seeders de localização..."

# Executar seeders de localização
echo "🏛️ Executando StateSeeder..."
php artisan db:seed --class=StateSeeder

echo "🏙️ Executando LocationSeeder..."
php artisan db:seed --class=LocationSeeder

echo "✅ Seeders de localização executados com sucesso!"
echo ""
echo "📋 Dados inseridos:"
echo "   • Estados brasileiros"
echo "   • Cidades principais de cada estado"
echo ""
echo "🌍 O sistema agora tem dados de localização para funcionamento!"
