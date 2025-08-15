# Seeder para Travestis e Garotos de Programa

## 📋 Descrição

Esta seeder cria usuários de exemplo para travestis (`transvestite`) e garotos de programa (`male_escort`) no sistema DesejoLivre. Todos os usuários criados são verificados e possuem perfis completos.

## 🚀 Como Executar

### Opção 1: Comando Artisan Personalizado (Recomendado)

```bash
# Executar com confirmação
php artisan seed:transvestites-male-escorts

# Executar sem confirmação
php artisan seed:transvestites-male-escorts --force
```

### Opção 2: Seeder Padrão

```bash
# Executar apenas esta seeder
php artisan db:seed --class=TransvestiteMaleEscortSeeder

# Executar todas as seeders (incluindo esta)
php artisan db:seed
```

### Opção 3: Via Tinker

```php
php artisan tinker

// No Tinker
$seeder = new Database\Seeders\TransvestiteMaleEscortSeeder();
$seeder->run();
```

## 👥 Usuários Criados

### Travestis (user_type: transvestite)

| Email | Nome Artístico | Idade | Características |
|-------|----------------|-------|-----------------|
| travesti1@teste.com | Valentina | 25 | Loira, azul, tatuagens, silicone |
| travesti2@teste.com | Bianca | 28 | Castanha, parda, piercing, silicone |
| travesti3@teste.com | Carolina | 23 | Ruiva, verde, tatuagens, natural |

### Garotos de Programa (user_type: male_escort)

| Email | Nome Artístico | Idade | Características |
|-------|----------------|-------|-----------------|
| garoto1@teste.com | Rafael | 26 | Castanho, atlético, tatuagens |
| garoto2@teste.com | Lucas | 24 | Loiro, azul, piercing |
| garoto3@teste.com | Diego | 29 | Preto, experiente, tatuagens |

## 🔐 Credenciais de Acesso

- **Senha para todos**: `password`
- **Status**: Todos os usuários estão ativos e verificados
- **Verificação**: Todos possuem verificação facial aprovada

## 📍 Localizações

A seeder cria usuários em múltiplas cidades:
- **São Paulo (SP)**
- **Rio de Janeiro (RJ)**
- **Belo Horizonte (MG)**

Cada usuário é associado a um bairro específico da cidade.

## 🏗️ Estrutura dos Dados

### Campos do Usuário
- `name`: Nome real
- `email`: Email único
- `user_type`: 'transvestite' ou 'male_escort'
- `phone`: Telefone de contato
- `active`: Status ativo
- `verified`: Verificação aprovada

### Campos do Perfil
- `artistic_name`: Nome artístico
- `age`: Idade
- `about_me`: Descrição pessoal
- `height`: Altura em cm
- `weight`: Peso em kg
- `hair_color`: Cor do cabelo
- `eye_color`: Cor dos olhos
- `ethnicity`: Etnia
- `has_tattoos`: Possui tatuagens
- `has_piercings`: Possui piercings
- `has_silicone`: Possui silicone (para travestis)
- `is_smoker`: É fumante
- `attends_home`: Atende em casa
- `travel_radius_km`: Raio de deslocamento
- `whatsapp`: Número do WhatsApp
- `telegram`: Usuário do Telegram

## 🔧 Personalização

Para personalizar os dados criados, edite o arquivo:
```
database/seeders/TransvestiteMaleEscortSeeder.php
```

### Adicionar Novos Usuários

1. Adicione novos dados no array `$transvestites` ou `$maleEscorts`
2. Execute a seeder novamente
3. Use `--force` para sobrescrever dados existentes

### Modificar Características

- Altere as descrições em `about_me`
- Modifique idades, alturas e pesos
- Ajuste cores de cabelo e olhos
- Configure status de tatuagens, piercings, etc.

## ⚠️ Importante

- Esta seeder é destinada apenas para **desenvolvimento e testes**
- Em produção, use dados reais e apropriados
- Todos os usuários criados são fictícios
- As senhas são simples para facilitar testes

## 🧹 Limpeza

Para remover os dados criados:

```bash
# Via Tinker
php artisan tinker

// Remover usuários específicos
User::whereIn('user_type', ['transvestite', 'male_escort'])->delete();

// Ou remover por email
User::whereIn('email', [
    'travesti1@teste.com',
    'travesti2@teste.com',
    'travesti3@teste.com',
    'garoto1@teste.com',
    'garoto2@teste.com',
    'garoto3@teste.com'
])->delete();
```

## 📞 Suporte

Em caso de dúvidas ou problemas:
1. Verifique se todas as migrations foram executadas
2. Confirme se os modelos State, City e District existem
3. Verifique se o plano "Básico" existe na tabela plans
4. Consulte os logs do Laravel para erros específicos
