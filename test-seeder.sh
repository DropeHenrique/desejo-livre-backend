#!/bin/bash

echo "🧪 Testando Seeder de Travestis e Garotos de Programa"
echo "=================================================="

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script no diretório raiz do Laravel"
    exit 1
fi

echo "📋 Verificando estrutura do projeto..."
echo "✅ Laravel detectado"

echo ""
echo "🔍 Verificando se a seeder existe..."
if [ -f "database/seeders/TransvestiteMaleEscortSeeder.php" ]; then
    echo "✅ TransvestiteMaleEscortSeeder encontrada"
else
    echo "❌ TransvestiteMaleEscortSeeder não encontrada"
    exit 1
fi

echo ""
echo "🔍 Verificando se o comando personalizado existe..."
if [ -f "app/Console/Commands/SeedTransvestitesAndMaleEscorts.php" ]; then
    echo "✅ Comando personalizado encontrado"
else
    echo "❌ Comando personalizado não encontrado"
fi

echo ""
echo "🚀 Executando seeder..."
php artisan seed:transvestites-male-escorts --force

echo ""
echo "🔍 Verificando usuários criados..."
php artisan tinker --execute="
echo 'Usuários travestis:';
echo User::where('user_type', 'transvestite')->count();
echo 'Usuários garotos de programa:';
echo User::where('user_type', 'male_escort')->count();
echo 'Total de usuários:';
echo User::count();
"

echo ""
echo "✅ Teste concluído!"
echo ""
echo "📋 Para acessar os usuários criados:"
echo "   • Travestis: travesti1@teste.com, travesti2@teste.com, travesti3@teste.com"
echo "   • Garotos: garoto1@teste.com, garoto2@teste.com, garoto3@teste.com"
echo "   • Senha: password"
echo ""
echo "🌐 Para testar no frontend, acesse:"
echo "   • /travestis - Lista de travestis"
echo "   • /garotos - Lista de garotos de programa"
