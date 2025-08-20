#!/bin/bash

# Script para limpar o banco e executar todos os seeders de desenvolvimento
# Uso: ./scripts/fresh-dev-seeders.sh

echo "🚀 Iniciando seeders de desenvolvimento com banco limpo..."

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Execute este script do diretório raiz do projeto Laravel"
    exit 1
fi

# Perguntar se o usuário quer limpar o banco
echo "⚠️  ATENÇÃO: Este script irá limpar todos os dados do banco de dados!"
echo "   Isso inclui usuários, perfis, mídia, etc."
echo ""
read -p "🤔 Deseja continuar? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Operação cancelada pelo usuário"
    exit 1
fi

echo "🧹 Limpando banco de dados..."
./scripts/clean-database.sh

if [ $? -ne 0 ]; then
    echo "❌ Erro ao limpar o banco de dados"
    exit 1
fi

echo ""
echo "🌱 Executando seeders..."
./scripts/run-dev-seeders.sh

if [ $? -ne 0 ]; then
    echo "❌ Erro ao executar os seeders"
    exit 1
fi

echo ""
echo "🎉 Processo concluído com sucesso!"
echo "🚀 O sistema está pronto para desenvolvimento!"
