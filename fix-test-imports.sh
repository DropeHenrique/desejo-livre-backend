#!/bin/bash

# Script para adicionar imports necessários nos arquivos de teste
echo "Adicionando imports necessários nos arquivos de teste..."

# Lista de arquivos que precisam do import do atributo Test
files=(
    "tests/Feature/CompanionProfileControllerTest.php"
    "tests/Feature/ServiceTypeControllerTest.php"
    "tests/Feature/GeographyControllerTest.php"
    "tests/Feature/SubscriptionControllerTest.php"
    "tests/Feature/UserControllerTest.php"
    "tests/Feature/ReviewControllerTest.php"
    "tests/Feature/FavoriteControllerTest.php"
    "tests/Feature/CepControllerTest.php"
    "tests/Feature/PlanControllerTest.php"
    "tests/Feature/BlogControllerTest.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "Processando $file..."

        # Verificar se o arquivo já tem o import
        if ! grep -q "use PHPUnit\\Framework\\Attributes\\Test;" "$file"; then
            # Adicionar o import após as outras linhas de use
            sed -i '/^use /a use PHPUnit\\Framework\\Attributes\\Test;' "$file"
            echo "  - Import adicionado"
        else
            echo "  - Import já existe"
        fi
    fi
done

echo "Imports adicionados!"
