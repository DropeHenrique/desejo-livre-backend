#!/bin/bash

# Script para corrigir permissões para testes
echo "Corrigindo permissões para testes..."

# Corrigir permissões do diretório de testes
chmod -R 755 tests/

# Corrigir permissões do cache do PHPUnit
chmod 777 .phpunit.result.cache 2>/dev/null || true

# Corrigir permissões do storage
chmod -R 777 storage/

# Corrigir permissões do bootstrap/cache
chmod -R 777 bootstrap/cache/

# Corrigir permissões do diretório vendor
chmod -R 755 vendor/

echo "Permissões corrigidas!"
