#!/bin/bash

# Script para limpar o banco de dados antes de executar os seeders
# Uso: ./scripts/clean-database.sh

echo "🧹 Limpando banco de dados..."

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

echo "🗑️ Removendo dados existentes..."

# Limpar tabelas relacionadas aos seeders
php artisan tinker --execute="
    // Limpar tabelas de mídia
    DB::table('media')->delete();
    echo '✅ Mídia removida' . PHP_EOL;

    // Limpar tabelas de perfis de acompanhantes
    DB::table('companion_profiles')->delete();
    echo '✅ Perfis de acompanhantes removidos' . PHP_EOL;

    // Limpar tabelas de relacionamentos
    DB::table('companion_districts')->delete();
    echo '✅ Relacionamentos companion_districts removidos' . PHP_EOL;

    // Limpar tabelas de usuários (exceto admin)
    DB::table('users')->where('user_type', '!=', 'admin')->delete();
    echo '✅ Usuários não-admin removidos' . PHP_EOL;

    // Limpar tabelas de assinaturas
    DB::table('subscriptions')->delete();
    echo '✅ Assinaturas removidas' . PHP_EOL;

    // Limpar tabelas de tickets e denúncias
    DB::table('tickets')->delete();
    echo '✅ Tickets removidos' . PHP_EOL;
    DB::table('reports')->delete();
    echo '✅ Denúncias removidas' . PHP_EOL;

    // Limpar tabelas de blog
    DB::table('blog_posts')->delete();
    echo '✅ Posts do blog removidos' . PHP_EOL;

    // Limpar tabelas de bairros
    DB::table('districts')->delete();
    echo '✅ Bairros removidos' . PHP_EOL;

    // Limpar tabelas de cidades
    DB::table('cities')->delete();
    echo '✅ Cidades removidas' . PHP_EOL;

    // Limpar tabelas de estados
    DB::table('states')->delete();
    echo '✅ Estados removidos' . PHP_EOL;

    echo '✅ Banco de dados limpo com sucesso!' . PHP_EOL;
"

echo "✅ Limpeza concluída!"
echo "🚀 Agora você pode executar os seeders com: ./scripts/run-dev-seeders.sh"
