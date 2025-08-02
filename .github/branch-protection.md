# 🔒 Configuração de Proteção de Branches

## Branches Protegidas
- `main` / `master`
- `develop`

## Configurações Necessárias

### 1. No GitHub Repository Settings:

1. Vá para **Settings** > **Branches**
2. Clique em **Add rule** para cada branch protegida
3. Configure as seguintes regras:

#### Para `main` e `master`:
- ✅ **Require a pull request before merging**
- ✅ **Require status checks to pass before merging**
  - Selecione: `laravel-tests`
- ✅ **Require branches to be up to date before merging**
- ✅ **Require conversation resolution before merging**
- ✅ **Require signed commits**
- ✅ **Require linear history**
- ✅ **Include administrators**

#### Para `develop`:
- ✅ **Require a pull request before merging**
- ✅ **Require status checks to pass before merging**
  - Selecione: `laravel-tests`
- ✅ **Require branches to be up to date before merging**
- ✅ **Require conversation resolution before merging**
- ✅ **Include administrators**

### 2. Status Checks Obrigatórios:
- `laravel-tests` (criado pelo GitHub Actions)

### 3. Workflow de Desenvolvimento:

```bash
# 1. Criar feature branch
git checkout -b feature/nova-funcionalidade

# 2. Desenvolver e commitar
git add .
git commit -m "feat: adiciona nova funcionalidade"

# 3. Push para o repositório
git push origin feature/nova-funcionalidade

# 4. Criar Pull Request para develop
# 5. Aguardar testes passarem
# 6. Merge após aprovação

# 7. Para release, criar PR de develop para main
```

## ⚠️ Importante:
- **Nenhum merge direto** nas branches protegidas
- **Todos os testes devem passar** antes do merge
- **Code review obrigatório** para todas as mudanças
- **Commits assinados** obrigatórios para main/master
