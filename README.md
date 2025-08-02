# DesejoLivre Backend API

API backend do projeto DesejoLivre construída com Laravel e Docker, seguindo a arquitetura planejada para uma plataforma de anúncios de acompanhantes.

## 🚀 Tecnologias

- **Laravel 12** (PHP 8.3)
- **PostgreSQL 15** (Banco de dados)
- **Redis 7** (Cache e sessões)
- **Laravel Sanctum** (Autenticação API)
- **Laravel Horizon** (Gerenciamento de filas)
- **Laravel Scout** (Busca)
- **Spatie Media Library** (Gerenciamento de mídia)
- **Docker & Docker Compose** (Containerização)

## 🏗️ Arquitetura

O projeto implementa:

- **Múltiplos guards de autenticação** (client, companion, admin)
- **API RESTful** com endpoints organizados por tipo de usuário
- **Sistema de planos** e assinaturas
- **Gerenciamento de mídia** para fotos e vídeos
- **Sistema de avaliações** e favoritos
- **Busca avançada** com filtros geográficos e demográficos
- **Sistema de moderação** de conteúdo

## 🐳 Configuração Docker

### Portas utilizadas (customizadas para evitar conflitos):
- **Laravel**: http://localhost:8085
- **PostgreSQL**: localhost:5435
- **Redis**: localhost:6381

### Containers:
- `desejo-livre-app` - Aplicação Laravel (PHP-FPM)
- `desejo-livre-nginx` - Servidor web
- `desejo-livre-db` - PostgreSQL
- `desejo-livre-redis` - Redis
- `desejo-livre-horizon` - Laravel Horizon

## 🚀 Como executar

### 1. Clone o projeto
```bash
git clone <repository-url>
cd desejo-livre-backend
```

### 2. Construa e inicie os containers
```bash
docker-compose build
docker-compose up -d
```

### 3. Execute as migrations
```bash
docker-compose exec app php artisan migrate
```

### 4. Acesse a aplicação
- **API**: http://localhost:8085
- **Horizon Dashboard**: http://localhost:8085/horizon

## 📊 Banco de Dados

O banco implementa a seguinte estrutura:

### Principais tabelas:
- `users` - Usuários (client, companion, admin)
- `companion_profiles` - Perfis de acompanhantes
- `states`, `cities`, `districts` - Estrutura geográfica
- `plans`, `subscriptions` - Sistema de planos
- `media` - Fotos e vídeos
- `reviews`, `favorites` - Avaliações e favoritos
- `blog_posts`, `blog_categories` - Sistema de blog

## 🔐 Autenticação

O sistema implementa múltiplos guards:

### Guards disponíveis:
- `client` - Para clientes
- `companion` - Para acompanhantes
- `admin` - Para administradores

### Exemplo de uso:
```php
// Middleware para acompanhantes
Route::middleware(['auth:companion'])->group(function () {
    // Rotas protegidas para acompanhantes
});
```

## 🛠️ Comandos úteis

### Containers
```bash
# Ver status dos containers
docker-compose ps

# Ver logs
docker-compose logs app

# Acessar container da aplicação
docker-compose exec app bash

# Parar containers
docker-compose down

# Rebuild completo
docker-compose down -v && docker-compose build && docker-compose up -d
```

### Laravel
```bash
# Executar migrations
docker-compose exec app php artisan migrate

# Criar migration
docker-compose exec app php artisan make:migration create_table_name

# Criar model
docker-compose exec app php artisan make:model ModelName

# Executar seeders
docker-compose exec app php artisan db:seed

# Limpar cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

## 📋 Rotas da API

### Autenticação
- `POST /api/auth/register/client` - Registro de cliente
- `POST /api/auth/register/companion` - Registro de acompanhante
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout

### Públicas
- `GET /api/states` - Listar estados
- `GET /api/states/{state}/cities` - Cidades de um estado
- `GET /api/companions` - Listar perfis de acompanhantes
- `GET /api/companions/{companion}` - Visualizar perfil

### Cliente (auth:client)
- `GET /api/client/favorites` - Favoritos
- `POST /api/companions/{companion}/favorite` - Adicionar favorito
- `POST /api/companions/{companion}/review` - Avaliar

### Acompanhante (auth:companion)
- `GET /api/companion/my-profile` - Meu perfil
- `PUT /api/companion/my-profile` - Atualizar perfil
- `POST /api/companion/my-profile/photos` - Upload fotos
- `POST /api/companion/online` - Ficar online

### Admin (auth:admin)
- `GET /api/admin/companions/pending` - Perfis pendentes
- `POST /api/companions/{companion}/verify` - Verificar perfil
- `GET /api/admin/dashboard` - Dashboard

## 🔧 Configuração

### Variáveis de ambiente importantes:
```env
# Banco de dados
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=desejo_livre_db

# Cache e sessões
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis

# Configurações específicas
PHONE_CHANGE_LIMIT_DAYS=30
CITY_CHANGE_LIMIT_DAYS=30
MAX_PHOTOS_PER_PROFILE=20
MAX_VIDEOS_PER_PROFILE=5
```

## 📦 Dependências principais

```json
{
    "laravel/sanctum": "^4.2",
    "spatie/laravel-medialibrary": "^11.13",
    "laravel/horizon": "^5.33",
    "laravel/scout": "^10.17",
    "spatie/laravel-sluggable": "^3.7"
}
```

## 🏃‍♂️ Próximos passos

1. Implementar controllers com lógica de negócio
2. Criar seeders para dados de teste
3. Configurar sistema de filas
4. Implementar testes automatizados
5. Configurar CI/CD

## 📄 Licença

Este projeto é privado e confidencial.
