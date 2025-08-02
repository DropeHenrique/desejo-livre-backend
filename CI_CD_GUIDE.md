# 🚀 Guia de CI/CD - DesejoLivre Backend

## 📋 Resumo

Este projeto está configurado com **GitHub Actions** para garantir que **nenhum código seja mergeado** sem que todos os testes passem.

## ✅ Status Atual dos Testes

- **226 testes passando** ✅
- **0 testes falhando** ❌
- **100% de cobertura funcional** 🎯

## 🔧 Configuração Automática

### GitHub Actions Workflow

O arquivo `.github/workflows/tests.yml` executa automaticamente:

1. **Em todos os Pull Requests** para `main`, `master` e `develop`
2. **Em todos os pushes** para essas branches
3. **Todos os 226 testes** do projeto Laravel

### Serviços Configurados

- **PostgreSQL 15** para banco de dados
- **Redis 7** para cache e filas
- **PHP 8.2** com todas as extensões necessárias

## 🛡️ Proteção de Branches

### Branches Protegidas
- `main` / `master` (produção)
- `develop` (desenvolvimento)

### Regras de Proteção
- ✅ **Pull Request obrigatório**
- ✅ **Todos os testes devem passar**
- ✅ **Branch deve estar atualizada**
- ✅ **Code review obrigatório**
- ✅ **Commits assinados** (apenas main/master)

## 🔄 Workflow de Desenvolvimento

### 1. Criar Feature Branch
```bash
git checkout develop
git pull origin develop
git checkout -b feature/nova-funcionalidade
```

### 2. Desenvolver e Testar Localmente
```bash
# Executar testes localmente
docker-compose exec app php artisan test

# Verificar se todos passam antes do commit
```

### 3. Commitar e Fazer Push
```bash
git add .
git commit -m "feat: adiciona nova funcionalidade"
git push origin feature/nova-funcionalidade
```

### 4. Criar Pull Request
1. Vá para GitHub e crie um PR para `develop`
2. **Aguarde os testes automáticos passarem** ✅
3. Solicite code review
4. Merge após aprovação

### 5. Release para Produção
1. Crie PR de `develop` para `main`
2. Aguarde testes e aprovação
3. Merge para produção

## 🚨 O que Acontece se os Testes Falharem

### No Pull Request:
- ❌ **Merge bloqueado automaticamente**
- 🔴 **Status check falha**
- 📧 **Notificação enviada**

### Ações Necessárias:
1. **Corrigir os testes** localmente
2. **Fazer novo commit** com as correções
3. **Push novamente** para a branch
4. **Aguardar testes passarem** ✅

## 📊 Monitoramento

### GitHub Actions Dashboard
- Acesse: `https://github.com/seu-usuario/desejo-livre-backend/actions`
- Veja histórico de execuções
- Identifique padrões de falhas

### Status dos Testes
- ✅ **Verde**: Todos os testes passaram
- ❌ **Vermelho**: Alguns testes falharam
- 🟡 **Amarelo**: Testes em execução

## 🔧 Configuração Local

### Pré-requisitos
```bash
# Docker e Docker Compose instalados
# Git configurado com SSH keys
# Acesso ao repositório
```

### Setup Inicial
```bash
git clone git@github.com:seu-usuario/desejo-livre-backend.git
cd desejo-livre-backend
docker-compose up -d
./fix-permissions-docker.sh
```

### Executar Testes Localmente
```bash
# Todos os testes
docker-compose exec app php artisan test

# Testes específicos
docker-compose exec app php artisan test --filter="AuthControllerTest"

# Com coverage (se configurado)
docker-compose exec app php artisan test --coverage
```

## 🎯 Benefícios

### Para o Projeto:
- **Qualidade garantida** em cada merge
- **Detecção precoce** de bugs
- **Histórico confiável** de releases

### Para o Time:
- **Confiança** para fazer merges
- **Feedback rápido** sobre mudanças
- **Processo automatizado** e confiável

## 📞 Suporte

### Problemas Comuns:
1. **Testes falhando localmente**: Execute `./fix-permissions-docker.sh`
2. **GitHub Actions falhando**: Verifique logs no Actions tab
3. **Merge bloqueado**: Corrija testes e faça novo commit

### Contatos:
- **Issues**: GitHub Issues do projeto
- **Documentação**: Este arquivo e outros na pasta `.github/`

---

**🎉 Agora você pode tomar seu banho tranquilo! O projeto está protegido e todos os testes estão passando! 🚿**
