# 🌱 Guia de Seeders - DesejoLivre Backend

Este documento explica como usar os scripts de seeders para configurar o banco de dados.

## 📋 Scripts Disponíveis

### 1. **Desenvolvimento Completo** - `run-dev-seeders.sh`
Executa TODOS os seeders incluindo dados de teste.

**Uso:**
```bash
./scripts/run-dev-seeders.sh
```

**O que executa:**
- ✅ Estados e cidades
- ✅ Planos e serviços
- ✅ Usuários de teste (admin, clientes, acompanhantes)
- ✅ Perfis de acompanhantes
- ✅ Conteúdo de blog e mídia
- ✅ Assinaturas de teste
- ✅ Dados de exemplo gerais

**Quando usar:** Ambiente de desenvolvimento, testes, demonstrações

---

### 2. **Localização Apenas** - `run-location-seeders.sh`
Executa APENAS os seeders de localização (estados e cidades).

**Uso:**
```bash
./scripts/run-location-seeders.sh
```

**O que executa:**
- ✅ Estados brasileiros
- ✅ Cidades principais de cada estado

**Quando usar:** Quando você só precisa dos dados de localização

---

### 3. **Produção** - `run-production-seeders.sh`
Executa APENAS os seeders essenciais para produção.

**Uso:**
```bash
./scripts/run-production-seeders.sh
```

**O que executa:**
- ✅ Estados e cidades
- ✅ Planos de assinatura
- ✅ Tipos de serviços
- ✅ Distritos de acompanhantes
- ✅ Usuário administrador

**Quando usar:** Ambiente de produção, quando não quer dados de teste

---

## 🚀 Como Executar

### Pré-requisitos
1. ✅ Laravel instalado e configurado
2. ✅ Banco de dados configurado e acessível
3. ✅ Migrações executadas (`php artisan migrate`)

### Execução
```bash
# Navegar para o diretório do projeto
cd desejo-livre-backend

# Executar o script desejado
./scripts/run-dev-seeders.sh      # Desenvolvimento completo
./scripts/run-location-seeders.sh  # Apenas localização
./scripts/run-production-seeders.sh # Produção
```

### Execução Manual (Alternativa)
Se preferir executar manualmente:

```bash
# Desenvolvimento completo
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=ServiceTypeSeeder
php artisan db:seed --class=CompanionDistrictSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=CompanionProfileSeeder
php artisan db:seed --class=CompanionServiceSeeder
php artisan db:seed --class=TransvestiteMaleEscortSeeder
php artisan db:seed --class=BlogSeeder
php artisan db:seed --class=MediaSeeder
php artisan db:seed --class=TestSubscriptionSeeder
php artisan db:seed --class=SampleDataSeeder

# Apenas localização
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=LocationSeeder

# Produção
php artisan db:seed --class=StateSeeder
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=ServiceTypeSeeder
php artisan db:seed --class=CompanionDistrictSeeder
php artisan db:seed --class=AdminUserSeeder
```

---

## 🔧 Solução de Problemas

### Erro: "Não foi possível conectar ao banco"
- Verifique se o banco está rodando
- Verifique as configurações em `.env`
- Teste a conexão: `php artisan tinker`

### Erro: "Execute este script do diretório raiz"
- Certifique-se de estar em `desejo-livre-backend/`
- O arquivo `artisan` deve estar visível

### Erro: "Class not found"
- Verifique se os seeders existem em `database/seeders/`
- Execute `composer dump-autoload`

---

## 📊 Ordem de Execução Recomendada

1. **Primeira vez (desenvolvimento):**
   ```bash
   ./scripts/run-dev-seeders.sh
   ```

2. **Apenas localização:**
   ```bash
   ./scripts/run-location-seeders.sh
   ```

3. **Produção:**
   ```bash
   ./scripts/run-production-seeders.sh
   ```

---

## 🎯 Dicas

- **Desenvolvimento:** Use `run-dev-seeders.sh` para ter todos os dados de teste
- **Produção:** Use `run-production-seeders.sh` para apenas dados essenciais
- **Localização:** Use `run-location-seeders.sh` quando só precisar de estados/cidades
- **Backup:** Sempre faça backup antes de executar seeders em produção
- **Teste:** Teste os scripts em ambiente de desenvolvimento primeiro

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs do Laravel
2. Teste a conexão com o banco
3. Verifique se todas as migrações foram executadas
4. Consulte a documentação do Laravel sobre seeders
