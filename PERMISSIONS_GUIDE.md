# 🔐 Guia de Permissões - DesejoLivre Backend

## 🚨 Problema Comum

**Erro:** `The stream or file "/var/www/storage/logs/laravel.log" could not be opened in append mode: Failed to open stream: Permission denied`

Este erro ocorre quando o Laravel não consegue escrever nos diretórios necessários devido a permissões incorretas.

## ⚡ Solução Rápida

### 🐳 Para Docker (Recomendado):
```bash
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh
```

### 🏠 Para Desenvolvimento Local:
```bash
./fix-permissions-local.sh
```

### 🌐 Para Produção (servidor web):
```bash
./fix-permissions.sh
```

## 🐳 Configuração Docker (Principal)

Como o projeto usa Docker, esta é a configuração principal:

### 1. Script Completo
```bash
# Executar setup completo do Docker
docker-compose up -d
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh
```

### 2. O que o script faz:
- ✅ Cria estrutura de diretórios
- ✅ Define permissões corretas (775)
- ✅ Configura .env para Docker (Redis, PostgreSQL)
- ✅ Limpa todos os caches
- ✅ Executa migrations
- ✅ Popula estados e planos

### 3. Credenciais Docker:
```env
# Banco PostgreSQL
DB_HOST=db
DB_PORT=5432
DB_DATABASE=desejo_livre_db
DB_USERNAME=desejo_livre_user
DB_PASSWORD=desejo_livre_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### 4. Acessos:
- **Aplicação:** http://localhost:8085
- **API Docs:** http://localhost:8085/docs/api
- **PostgreSQL:** localhost:5435
- **Redis:** localhost:6381

## 🏠 Desenvolvimento vs 🌐 Produção

### Desenvolvimento Local
- **Usuário:** Seu usuário atual (ex: `pedro:pedro`)
- **Script:** `fix-permissions-local.sh`
- **Uso:** `php artisan serve`, desenvolvimento local

### Docker (Principal)
- **Usuário:** `www-data:www-data` (33:33)
- **Script:** `fix-permissions-docker.sh`
- **Uso:** Ambiente principal do projeto

### Produção (Apache/Nginx)
- **Usuário:** `www-data:www-data` (servidor web)
- **Script:** `fix-permissions.sh`
- **Uso:** Servidor Apache, Nginx, produção

## 🔧 Solução Manual

### Para Docker:
```bash
# Entrar no container como root
docker exec -u root desejo-livre-app bash

# Criar estrutura
mkdir -p storage/framework/{cache/data,sessions,testing,views}
mkdir -p storage/app/{public,temp}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Definir permissões
chmod -R 775 storage/ bootstrap/cache/

# Configurar .env
cp env.docker .env
chown www-data:www-data .env

# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Para Desenvolvimento Local:
```bash
sudo chown -R $USER:$USER storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/
```

### Para Produção:
```bash
sudo chown -R www-data:www-data storage/ bootstrap/cache/
sudo chmod -R 775 storage/ bootstrap/cache/
```

## 📁 Estrutura de Permissões

### Diretórios que precisam de escrita:
- `storage/` - Logs, cache, sessões, uploads
- `bootstrap/cache/` - Cache de configuração e rotas
- `storage/logs/` - Arquivos de log
- `storage/app/` - Arquivos da aplicação
- `storage/framework/` - Cache do framework

### Permissões Recomendadas:
- **Diretórios:** `775` (rwxrwxr-x)
- **Arquivos:** `664` (rw-rw-r--)
- **Owner Docker:** `www-data:www-data` (33:33)
- **Owner Desenvolvimento:** `$USER:$USER` (seu usuário)
- **Owner Produção:** `www-data:www-data` (usuário do servidor web)

## ⚠️ Problemas Comuns

### 1. Docker vs Local
**Problema:** Confusão entre ambiente Docker e local.

**Solução:** Use sempre Docker para este projeto:
```bash
# ✅ Correto (Docker)
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh

# ❌ Evitar (Local)
php artisan serve
```

### 2. Configuração Redis/PostgreSQL
**Problema:** Configuração incorreta para Docker.

**Solução:** Use as configurações do `env.docker`:
```env
DB_HOST=db         # Não localhost
REDIS_HOST=redis   # Não localhost
```

### 3. Conflito de Portas
**Problema:** Servidor local conflitando com Docker.

**Solução:**
```bash
# Parar processos locais
pkill -f "php artisan serve"

# Usar apenas Docker
docker-compose up -d
```

## 📝 Scripts Disponíveis

### 🐳 Docker (Principal)
```bash
# Setup completo
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh

# Somente permissões
docker exec desejo-livre-app bash /var/www/fix-permissions-docker.sh
```

### 🏠 Desenvolvimento Local
```bash
chmod +x fix-permissions-local.sh
./fix-permissions-local.sh
```

### 🌐 Produção
```bash
chmod +x fix-permissions.sh
./fix-permissions.sh
```

## 🛡️ Segurança

### ⚠️ NUNCA faça:
- `chmod 777` em produção
- `chown root:root` nos diretórios do Laravel
- Executar aplicação como root

### ✅ Boas práticas:
- Use `775` para diretórios, `664` para arquivos
- Owner sempre como usuário apropriado
- Mantenha `.env` com permissões restritivas (`664`)
- Use Docker para desenvolvimento e produção

## 🔄 Workflow Recomendado

### Setup inicial:
```bash
# 1. Subir containers
docker-compose up -d

# 2. Configurar aplicação
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh

# 3. Acessar aplicação
open http://localhost:8085
```

### Desenvolvimento diário:
```bash
# Aplicação
http://localhost:8085

# API Estados
http://localhost:8085/api/geography/states

# API Planos
http://localhost:8085/api/plans
```

### Após mudanças:
```bash
# Limpar caches
docker exec desejo-livre-app php artisan cache:clear

# Reconfigurar se necessário
docker exec -u root desejo-livre-app bash /var/www/fix-permissions-docker.sh
```

## 🚀 Integração com CI/CD

```yaml
# .github/workflows/deploy.yml
- name: Fix Laravel Permissions (Docker)
  run: |
    docker-compose up -d
    docker exec -u root app bash /var/www/fix-permissions-docker.sh
```

---

**💡 Resumo:**
- **Use Docker** para tudo (principal)
- **Script principal:** `fix-permissions-docker.sh`
- **Aplicação:** http://localhost:8085
- **API funcional** com estados e planos populados
- **Permissões corretas** para ambiente Docker
