#!/bin/bash

# Scripts úteis para desenvolvimento sem entrar no container

case "$1" in
    "artisan")
        docker-compose exec app php artisan "${@:2}"
        ;;
    "routes")
        docker-compose exec app php artisan route:list
        ;;
    "migrate")
        docker-compose exec app php artisan migrate
        ;;
    "migrate:fresh")
        docker-compose exec app php artisan migrate:fresh --seed
        ;;
    "tinker")
        docker-compose exec app php artisan tinker
        ;;
    "test")
        docker-compose exec app php artisan test
        ;;
    "composer")
        docker-compose exec app composer "${@:2}"
        ;;
    "logs")
        docker-compose logs -f app
        ;;
    "bash")
        docker-compose exec app bash
        ;;
    "up")
        docker-compose up -d
        ;;
    "down")
        docker-compose down
        ;;
    "restart")
        docker-compose restart app
        ;;
    "docs")
        echo "🚀 Abrindo documentação da API em português..."
        echo "📖 Acesse: http://localhost:8085/docs/api"
        if command -v xdg-open > /dev/null; then
            xdg-open "http://localhost:8085/docs/api"
        else
            echo "📱 Abra o link acima no seu navegador"
        fi
        ;;
    *)
        echo "Comandos disponíveis:"
        echo "  ./scripts.sh artisan [comando]     - Executa comando artisan"
        echo "  ./scripts.sh routes                - Lista todas as rotas"
        echo "  ./scripts.sh migrate               - Executa migrations"
        echo "  ./scripts.sh migrate:fresh         - Recria DB e executa seeds"
        echo "  ./scripts.sh tinker                - Abre tinker"
        echo "  ./scripts.sh test                  - Executa testes"
        echo "  ./scripts.sh composer [comando]    - Executa comando composer"
        echo "  ./scripts.sh logs                  - Mostra logs em tempo real"
        echo "  ./scripts.sh bash                  - Acessa bash do container"
        echo "  ./scripts.sh up                    - Sobe os containers"
        echo "  ./scripts.sh down                  - Para os containers"
        echo "  ./scripts.sh restart               - Reinicia o app"
        echo "  ./scripts.sh docs                  - Abre documentação da API"
        ;;
esac
