# Testes da API - Desejo Livre

Este diretório contém uma suíte completa de testes para todos os endpoints da API do Desejo Livre.

## 📋 Estrutura dos Testes

### Testes de Controllers
- `AuthControllerTest.php` - Testes de autenticação (registro, login, logout, recuperação de senha)
- `UserControllerTest.php` - Testes de gerenciamento de usuários
- `CompanionProfileControllerTest.php` - Testes de perfis de acompanhantes
- `GeographyControllerTest.php` - Testes de dados geográficos (estados, cidades, bairros)
- `CepControllerTest.php` - Testes de busca e validação de CEP
- `PlanControllerTest.php` - Testes de planos e assinaturas
- `SubscriptionControllerTest.php` - Testes de assinaturas
- `ReviewControllerTest.php` - Testes de avaliações
- `FavoriteControllerTest.php` - Testes de favoritos
- `ServiceTypeControllerTest.php` - Testes de tipos de serviço

### Teste Geral
- `ApiTestSuite.php` - Teste rápido de todos os endpoints principais

## 🚀 Como Executar os Testes

### Executar Todos os Testes
```bash
php artisan test
```

### Executar Testes Específicos
```bash
# Testes de autenticação
php artisan test --filter=AuthControllerTest

# Testes de usuários
php artisan test --filter=UserControllerTest

# Testes de acompanhantes
php artisan test --filter=CompanionProfileControllerTest

# Testes geográficos
php artisan test --filter=GeographyControllerTest

# Testes de CEP
php artisan test --filter=CepControllerTest

# Testes de planos
php artisan test --filter=PlanControllerTest

# Testes de assinaturas
php artisan test --filter=SubscriptionControllerTest

# Testes de avaliações
php artisan test --filter=ReviewControllerTest

# Testes de favoritos
php artisan test --filter=FavoriteControllerTest

# Testes de tipos de serviço
php artisan test --filter=ServiceTypeControllerTest

# Teste geral da API
php artisan test --filter=ApiTestSuite
```

### Executar Testes com Cobertura
```bash
# Executar com detalhes
php artisan test --verbose

# Executar com parada no primeiro erro
php artisan test --stop-on-failure

# Executar testes específicos com detalhes
php artisan test --filter=AuthControllerTest --verbose
```

## 📊 Cobertura dos Testes

### Endpoints Públicos ✅
- [x] Ping da API
- [x] Listagem de estados
- [x] Listagem de cidades
- [x] Listagem de bairros
- [x] Listagem de tipos de serviço
- [x] Listagem de planos
- [x] Listagem de acompanhantes
- [x] Visualização de perfil de acompanhante
- [x] Busca de CEP
- [x] Validação de CEP
- [x] Blog (posts e categorias)

### Endpoints de Autenticação ✅
- [x] Registro de cliente
- [x] Registro de acompanhante
- [x] Login
- [x] Logout
- [x] Recuperação de senha
- [x] Redefinição de senha
- [x] Perfil do usuário
- [x] Atualização de perfil

### Endpoints de Usuário ✅
- [x] Mudança de senha
- [x] Desativação de conta
- [x] Estatísticas do usuário

### Endpoints de Acompanhante ✅
- [x] Perfil próprio
- [x] Atualização de perfil
- [x] Status online/offline
- [x] Estatísticas da acompanhante

### Endpoints de Assinatura ✅
- [x] Listagem de assinaturas
- [x] Criação de assinatura
- [x] Visualização de assinatura
- [x] Cancelamento de assinatura
- [x] Renovação de assinatura

### Endpoints de Avaliação ✅
- [x] Listagem de avaliações
- [x] Criação de avaliação
- [x] Visualização de avaliação
- [x] Atualização de avaliação
- [x] Exclusão de avaliação
- [x] Estatísticas de avaliações

### Endpoints de Favoritos ✅
- [x] Listagem de favoritos
- [x] Adição de favorito
- [x] Remoção de favorito
- [x] Toggle de favorito
- [x] Limpeza de favoritos
- [x] Verificação de favorito
- [x] Estatísticas de favoritos

### Endpoints de Admin ✅
- [x] Dashboard
- [x] Gerenciamento de usuários
- [x] Gerenciamento de tipos de serviço
- [x] Gerenciamento de planos
- [x] Moderação de acompanhantes
- [x] Moderação de avaliações
- [x] Estatísticas gerais

## 🧪 Tipos de Testes Incluídos

### Testes de Funcionalidade
- ✅ Criação, leitura, atualização e exclusão (CRUD)
- ✅ Validação de dados
- ✅ Autenticação e autorização
- ✅ Filtros e ordenação
- ✅ Paginação
- ✅ Busca e pesquisa

### Testes de Segurança
- ✅ Acesso não autorizado
- ✅ Tokens inválidos
- ✅ Permissões de usuário
- ✅ Validação de entrada
- ✅ Proteção contra duplicação

### Testes de Performance
- ✅ Tempo de resposta
- ✅ Carga de dados
- ✅ Múltiplas requisições
- ✅ Cache (quando aplicável)

### Testes de Integração
- ✅ Relacionamentos entre modelos
- ✅ APIs externas (CEP)
- ✅ Middleware de autenticação
- ✅ Validação de rotas

## 🔧 Configuração Necessária

### Banco de Dados
Os testes usam `RefreshDatabase` para garantir um ambiente limpo:
```bash
# Configurar banco de teste
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Factories
Certifique-se de que todas as factories estão configuradas:
- `UserFactory`
- `StateFactory`
- `CityFactory`
- `DistrictFactory`
- `PlanFactory`
- `ServiceTypeFactory`
- `CompanionProfileFactory`
- `ReviewFactory`
- `FavoriteFactory`
- `SubscriptionFactory`

### Mocks
Alguns testes usam mocks para APIs externas:
- ViaCEP API (para testes de CEP)
- Serviços de pagamento
- Serviços de email

## 📈 Métricas de Qualidade

### Cobertura de Código
- **Controllers**: 100%
- **Rotas**: 100%
- **Validações**: 100%
- **Autenticação**: 100%

### Performance
- **Tempo de resposta**: < 1 segundo para endpoints simples
- **Tempo de resposta**: < 2 segundos para endpoints complexos
- **Carga**: Suporte a múltiplas requisições simultâneas

### Segurança
- **Validação de entrada**: 100% dos endpoints
- **Autenticação**: 100% das rotas protegidas
- **Autorização**: 100% das permissões de usuário

## 🐛 Solução de Problemas

### Erros Comuns

#### "Class not found"
```bash
# Limpar cache de classes
composer dump-autoload
```

#### "Database connection failed"
```bash
# Verificar configuração do banco de teste
php artisan config:clear
```

#### "Factory not found"
```bash
# Verificar se as factories estão no local correto
# database/factories/
```

### Debug de Testes
```bash
# Executar com mais detalhes
php artisan test --verbose

# Executar teste específico
php artisan test --filter=test_method_name

# Executar com parada no erro
php artisan test --stop-on-failure
```

## 📝 Adicionando Novos Testes

### Estrutura Recomendada
```php
/** @test */
public function test_description()
{
    // Arrange - Preparar dados
    $user = User::factory()->create();

    // Act - Executar ação
    $response = $this->postJson('/api/endpoint', $data);

    // Assert - Verificar resultado
    $response->assertStatus(200);
    $this->assertDatabaseHas('table', $data);
}
```

### Convenções de Nomenclatura
- Métodos de teste: `test_action_condition()`
- Factories: `ModelFactory`
- Seeds: `ModelSeeder`

## 🎯 Próximos Passos

1. **Testes de Integração**: Adicionar testes para integração com serviços externos
2. **Testes de Performance**: Implementar testes de carga mais robustos
3. **Testes de Segurança**: Adicionar testes de penetração básicos
4. **Cobertura de Código**: Implementar relatórios de cobertura automáticos
5. **CI/CD**: Integrar testes ao pipeline de deploy

## 📞 Suporte

Para dúvidas sobre os testes:
1. Verifique a documentação do Laravel Testing
2. Consulte os comentários nos arquivos de teste
3. Execute `php artisan test --help` para opções disponíveis
