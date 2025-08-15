# 🧪 Guia de Teste - Sistema de Planos

## 📋 Pré-requisitos

1. **Backend rodando** em Docker
2. **Frontend rodando** em `http://localhost:3000`
3. **Banco de dados** populado com dados de teste

## 🚀 Como Testar

### 1. Preparar o Banco de Dados

```bash
# Acessar o container do backend
docker exec -it desejo-livre-backend-app-1 bash

# Executar migrations
php artisan migrate:fresh

# Executar seeders
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=TestSubscriptionSeeder
```

### 2. Testar o Fluxo Completo

#### **Cenário 1: Usuário sem Plano**

1. **Acessar** `http://localhost:3000/login`
2. **Fazer login** com um usuário sem assinatura
3. **Ir para** `http://localhost:3000/planos`
4. **Escolher um plano** (ex: Premium)
5. **Clicar em "Escolher"**
6. **Selecionar PIX** como método de pagamento
7. **Clicar em "Gerar Código PIX"**
8. **Aguardar** o processamento simulado (3 segundos)
9. **Verificar** que foi redirecionado para `/perfil`
10. **Ir para a aba "Meu Plano"** e verificar as limitações

#### **Cenário 2: Usuário com Plano Ativo**

1. **Fazer login** com um usuário que já tem assinatura
2. **Ir para** `http://localhost:3000/perfil`
3. **Clicar na aba "Meu Plano"**
4. **Verificar** as limitações e funcionalidades disponíveis

### 3. Testar Limitações

#### **Teste de Favoritos (Clientes)**

1. **Fazer login** como cliente com plano Básico
2. **Ir para** `http://localhost:3000/acompanhantes`
3. **Tentar adicionar mais de 3 favoritos**
4. **Verificar** que aparece mensagem de limite excedido

#### **Teste de Fotos (Acompanhantes)**

1. **Fazer login** como acompanhante com plano Bronze
2. **Ir para** `http://localhost:3000/perfil`
3. **Tentar fazer upload de mais de 5 fotos**
4. **Verificar** que aparece mensagem de limite excedido

### 4. Testar APIs

#### **Verificar Limitações**

```bash
# GET /api/plan-limitations/all
curl -H "Authorization: Bearer SEU_TOKEN" \
     http://localhost:8000/api/plan-limitations/all
```

#### **Verificar Funcionalidade**

```bash
# POST /api/plan-limitations/check-feature
curl -X POST -H "Authorization: Bearer SEU_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"feature": "view_all_photos"}' \
     http://localhost:8000/api/plan-limitations/check-feature
```

#### **Verificar Limite**

```bash
# POST /api/plan-limitations/check-limit
curl -X POST -H "Authorization: Bearer SEU_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"feature": "favorites_limit"}' \
     http://localhost:8000/api/plan-limitations/check-limit
```

## 📊 Dados de Teste

### Usuários Disponíveis

- **Email:** `teste@desejolivre.com` | **Senha:** `password`
- **Email:** `cliente@desejolivre.com` | **Senha:** `password`
- **Email:** `acompanhante@desejolivre.com` | **Senha:** `password`

### Planos Disponíveis

#### **Para Clientes:**
- **Básico:** R$ 9,90 - 3 favoritos, 6 fotos por perfil
- **Premium:** R$ 19,90 - Favoritos ilimitados, todas as fotos
- **VIP:** R$ 39,90 - Favoritos ilimitados, todas as fotos

#### **Para Acompanhantes:**
- **Bronze:** R$ 29,90 - 5 fotos, 0 vídeos
- **Prata:** R$ 49,90 - 10 fotos, 0 vídeos, destaque na busca
- **Ouro:** R$ 79,90 - 20 fotos, 5 vídeos, destaque premium
- **Black:** R$ 129,90 - Fotos e vídeos ilimitados, suporte prioritário

## 🔍 Verificações Importantes

### 1. Frontend
- [ ] Página de planos carrega corretamente
- [ ] Comparação de planos mostra limitações corretas
- [ ] Formulário de pagamento funciona
- [ ] Redirecionamento após pagamento
- [ ] Aba "Meu Plano" mostra limitações
- [ ] Mensagens de erro para limites excedidos

### 2. Backend
- [ ] APIs de limitações respondem corretamente
- [ ] Middleware bloqueia ações quando necessário
- [ ] Criação de assinaturas funciona
- [ ] Verificação de planos ativos funciona
- [ ] Contagem de uso está correta

### 3. Integração
- [ ] Token de autenticação funciona
- [ ] Dados são sincronizados entre frontend e backend
- [ ] Erros são tratados adequadamente
- [ ] Loading states funcionam

## 🐛 Troubleshooting

### Problema: "Unauthenticated"
- Verificar se o token está sendo enviado
- Verificar se o token não expirou

### Problema: "404 Not Found"
- Verificar se as rotas estão registradas
- Verificar se o middleware está configurado

### Problema: Limitações não aparecem
- Verificar se o usuário tem assinatura ativa
- Verificar se o plano está ativo
- Verificar se as features estão configuradas

### Problema: Contagem incorreta
- Verificar se os relacionamentos estão corretos
- Verificar se a query está contando corretamente

## 📝 Logs Úteis

```bash
# Ver logs do backend
docker logs desejo-livre-backend-app-1

# Ver logs do frontend
npm run dev
```

## ✅ Checklist de Teste

- [ ] Login funciona
- [ ] Página de planos carrega
- [ ] Seleção de plano funciona
- [ ] Pagamento simulado funciona
- [ ] Redirecionamento funciona
- [ ] Limitações aparecem corretamente
- [ ] APIs respondem corretamente
- [ ] Middleware funciona
- [ ] Mensagens de erro aparecem
- [ ] Loading states funcionam
- [ ] Responsividade funciona
- [ ] Dark mode funciona

## 🎯 Próximos Passos

1. **Implementar gateway de pagamento real** (Mercado Pago/PagSeguro)
2. **Adicionar notificações** quando limites estão próximos
3. **Implementar upgrade automático** quando necessário
4. **Adicionar mais funcionalidades** específicas
5. **Criar dashboard** de uso para acompanhantes
