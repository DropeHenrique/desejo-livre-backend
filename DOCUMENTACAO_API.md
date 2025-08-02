# 📖 Documentação da API - DesejoLivre

## ✅ Documentação Traduzida para Português! 🇧🇷

A documentação da API foi completamente traduzida e personalizada para português brasileiro.

### 🚀 Como acessar

```bash
# Usando o script utilitário
./scripts.sh docs

# Ou acesse diretamente no navegador
http://localhost:8085/docs/api
```

### 📋 O que foi traduzido

#### ✅ Configuração Geral
- **Título**: "DesejoLivre - Documentação da API"
- **Descrição**: Explicação completa em português sobre a plataforma
- **Servidores**: Ambiente de desenvolvimento e produção nomeados em português

#### ✅ Grupos de Endpoints
- **🔐 Autenticação** - Login, registro de clientes e acompanhantes
- **👥 Acompanhantes** - Listagem e visualização de perfis
- **🌎 Geografia** - Estados, cidades e busca por localização

#### ✅ Documentação Detalhada dos Endpoints

**Autenticação:**
- `POST /api/auth/register/client` - Registrar novo cliente
- `POST /api/auth/register/companion` - Registrar nova acompanhante
- `POST /api/auth/login` - Fazer login

**Acompanhantes:**
- `GET /api/companions` - Listar perfis de acompanhantes
- `GET /api/companions/{slug}` - Exibir perfil específico

**Geografia:**
- `GET /api/geography/states` - Listar todos os estados
- `GET /api/geography/states/{id}` - Exibir estado específico
- `GET /api/geography/states/{id}/cities` - Listar cidades do estado
- `GET /api/geography/states/{id}/companions` - Acompanhantes do estado

### 📝 Exemplos em Português

Todos os exemplos de requisição e resposta foram traduzidos:

```json
{
  "message": "Cliente registrado com sucesso",
  "user": {
    "name": "João Silva",
    "email": "joao@exemplo.com",
    "user_type": "client"
  }
}
```

### 🔍 Parâmetros Traduzidos

- **search** → "Buscar por nome artístico"
- **city_id** → "Filtrar por cidade"
- **age_min/age_max** → "Idade mínima/máxima"
- **per_page** → "Resultados por página"

### 📱 Interface em Português

A interface do Scramble foi configurada com:
- Título personalizado em português
- Servidores nomeados como "Desenvolvimento" e "Produção"
- Layout responsivo otimizado
- Funcionalidade "Try It" habilitada

### 🛠️ Como adicionar nova documentação

Para novos endpoints, use o padrão estabelecido:

```php
/**
 * Título em português
 *
 * Descrição detalhada do que o endpoint faz.
 *
 * @group Categoria
 * @urlParam id int required Descrição do parâmetro. Example: 1
 * @queryParam search string Buscar por nome. Example: João
 * @response 200 {
 *   "message": "Sucesso",
 *   "data": {...}
 * }
 */
public function meuMetodo(Request $request): JsonResponse
```

### 🔄 Atualizando a Documentação

As alterações nos controllers são refletidas automaticamente na documentação devido ao hot reload já configurado.

### 📊 Funcionalidades Ativas

- ✅ **Try It** - Teste direto na documentação
- ✅ **Exemplos** - Requests e responses em português
- ✅ **Agrupamento** - Endpoints organizados por categoria
- ✅ **Responsivo** - Interface adaptável para mobile
- ✅ **Busca** - Encontre endpoints rapidamente

---

**🎉 Resultado:** Documentação profissional 100% em português brasileiro, pronta para uso!
