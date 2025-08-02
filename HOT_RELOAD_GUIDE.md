# Guia de Hot Reload - DesejoLivre Backend

## ✅ Problemas Resolvidos

### 1. Rotas da API não apareciam no `route:list`
**Problema:** O arquivo `bootstrap/app.php` não estava configurado para carregar as rotas da API.

**Solução:** Adicionada a linha `api: __DIR__.'/../routes/api.php'` na configuração de routing.

**Antes:** Apenas 28 rotas (só web e Horizon)
**Depois:** 77 rotas (incluindo todas as rotas da API)

### 2. Hot Reload já estava funcionando! 🎉
O Docker Compose já estava configurado corretamente com volumes mapeados:
```yaml
volumes:
  - ./:/var/www  # Mapeia o diretório atual para /var/www no container
```

## 🚀 Como usar o Hot Reload

### Scripts Úteis (sem entrar no container)
Criado o arquivo `scripts.sh` com comandos úteis:

```bash
# Listar todas as rotas
./scripts.sh routes

# Executar qualquer comando artisan
./scripts.sh artisan route:clear
./scripts.sh artisan config:cache

# Migrations
./scripts.sh migrate
./scripts.sh migrate:fresh

# Composer
./scripts.sh composer install
./scripts.sh composer require pacote/novo

# Testes
./scripts.sh test

# Ver logs em tempo real
./scripts.sh logs

# Outros comandos úteis
./scripts.sh tinker
./scripts.sh bash      # Acessa o container
./scripts.sh up        # Sobe containers
./scripts.sh down      # Para containers
./scripts.sh restart   # Reinicia app
```

### Como funciona o Hot Reload

1. **Arquivos PHP:** Alterações são refletidas imediatamente
2. **Rotas:** Novas rotas aparecem instantaneamente
3. **Controllers:** Mudanças em lógica são aplicadas na próxima requisição
4. **Configurações:** Use `./scripts.sh artisan config:cache` se necessário

### Testado e Funcionando ✅

- ✅ Adição/remoção de rotas
- ✅ Modificação de controllers
- ✅ Alterações em models
- ✅ Mudanças em middlewares
- ✅ Updates em migrations (com `./scripts.sh migrate`)

## 📝 Comandos Úteis

```bash
# Ver todas as rotas da API
./scripts.sh routes | grep api/

# Verificar status dos containers
docker-compose ps

# Acessar diretamente uma rota
curl http://localhost:8085/api/geography/states

# Ver logs do nginx (se houver problemas de roteamento)
docker-compose logs nginx
```

## 🔧 Estrutura dos Volumes

```yaml
# No docker-compose.yml
volumes:
  - ./:/var/www                                    # Código fonte
  - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini  # Config PHP
  - ./docker/nginx/:/etc/nginx/conf.d/             # Config Nginx
```

## ⚠️ Situações que requerem restart

- Mudanças no `.env`
- Alterações no `docker-compose.yml`
- Modificações nos arquivos de configuração do PHP/Nginx
- Instalação de novas extensões PHP

Para restart: `./scripts.sh restart` ou `docker-compose restart app`

---

**Resumo:** Seu ambiente já estava configurado para hot reload! O problema era só as rotas da API não estarem sendo carregadas devido à configuração faltante no `bootstrap/app.php`.
