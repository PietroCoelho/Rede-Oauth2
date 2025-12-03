# SDK PHP eRede com OAuth 2.0

SDK de integração com eRede utilizando autenticação OAuth 2.0 ao invés de Basic Auth.

## Funcionalidades

Este SDK possui as seguintes funcionalidades:

* Autenticação OAuth 2.0
* Autorização de transações
* Captura de transações
* Consulta de transações (por TID ou referência)
* Cancelamento de transações
* Parcelamento

## Requisitos

* PHP >= 8.1
* Docker e Docker Compose (para desenvolvimento)

## Instalação

### Via Composer

O pacote está disponível no [Packagist](https://packagist.org/packages/pietrocoelho/erede-php-oauth).


```bash
composer require pietrocoelho/erede-php-oauth
```

### Via Docker

```bash
# Construir a imagem Docker
make build

# Iniciar o container
make up

# Instalar dependências
make install

# Configurar .env para testes (opcional)
make setup-env
```

## Configuração

### Credenciais OAuth

Configure as credenciais OAuth diretamente no código:

```php
<?php
use Rede\Store;
use Rede\Environment;
use Rede\OAuth\OAuthClient;

// Configuração para sandbox
$oauthClient = new OAuthClient('https://rl7-sandbox-api.useredecloud.com.br/oauth2/token');
$store = new Store('MERCHANT_ID', 'MERCHANT_KEY', Environment::sandbox(), $oauthClient);

// Configuração para produção
$oauthClient = new OAuthClient('https://api.userede.com.br/erede/oauth2/token');
$store = new Store('MERCHANT_ID', 'MERCHANT_KEY', Environment::production(), $oauthClient);
```

## Uso Básico

### Configuração Inicial

```php
<?php
use Rede\Store;
use Rede\Environment;
use Rede\OAuth\OAuthClient;

// Configuração da loja com OAuth em modo sandbox
$oauthClient = new OAuthClient('https://rl7-sandbox-api.useredecloud.com.br/oauth2/token');
$store = new Store('MERCHANT_ID', 'MERCHANT_KEY', Environment::sandbox(), $oauthClient);

// Para produção
$oauthClient = new OAuthClient('https://api.userede.com.br/erede/oauth2/token');
$store = new Store('MERCHANT_ID', 'MERCHANT_KEY', Environment::production(), $oauthClient);
```

### Autorizando uma transação

```php
<?php
use Rede\Store;
use Rede\Environment;
use Rede\Transaction;
use Rede\eRede;
use Rede\OAuth\OAuthClient;

// Configuração da loja
$oauthClient = new OAuthClient('https://rl7-sandbox-api.useredecloud.com.br/oauth2/token');
$store = new Store('MERCHANT_ID', 'MERCHANT_KEY', Environment::sandbox(), $oauthClient);

// Transação que será autorizada (sem captura automática)
$transaction = (new Transaction(20.99, 'pedido' . time()))->creditCard(
    '5448280000000007',
    '235',
    '12',
    '2025',
    'John Snow'
)->capture(false);

try {
    $response = (new eRede($store))->create($transaction);
    
    if ($response->getReturnCode() == '00') {
        printf("Transação autorizada com sucesso; tid=%s\n", $response->getTid());
    } else {
        printf("Transação recusada: %s\n", $response->getReturnMessage());
    }
} catch (\Exception $e) {
    printf("Erro: %s\n", $e->getMessage());
}
```

### Autorizando e capturando uma transação

```php
<?php
// Assumindo que $store já foi configurado anteriormente
// Transação com captura automática
$transaction = (new Transaction(20.99, 'pedido' . time()))->creditCard(
    '5448280000000007',
    '235',
    '12',
    '2025',
    'John Snow'
)->capture(true);

$response = (new eRede($store))->create($transaction);

if ($response->getReturnCode() == '00') {
    printf("Transação autorizada e capturada; tid=%s\n", $response->getTid());
}
```

### Transação com parcelamento

```php
<?php
$transaction = (new Transaction(100.00, 'pedido' . time()))->creditCard(
    '5448280000000007',
    '235',
    '12',
    '2025',
    'John Snow'
)->setInstallments(3);

$response = (new eRede($store))->create($transaction);

if ($response->getReturnCode() == '00') {
    printf("Transação parcelada em %dx; tid=%s\n", $response->getInstallments(), $response->getTid());
}
```

### Transação com informações adicionais (gateway e módulo)

```php
<?php
$transaction = (new Transaction(20.99, 'pedido' . time()))->creditCard(
    '5448280000000007',
    '235',
    '12',
    '2025',
    'John Snow'
)->additional(1234, 56);

$response = (new eRede($store))->create($transaction);
```

### Capturando uma transação pré-autorizada

```php
<?php
// Primeiro autoriza sem captura
$transaction = (new Transaction(20.99, 'pedido' . time()))->creditCard(
    '5448280000000007',
    '235',
    '12',
    '2025',
    'John Snow'
)->capture(false);

$response = (new eRede($store))->create($transaction);

// Depois captura usando o TID
$captureTransaction = (new Transaction(20.99))->setTid($response->getTid());
$captureResponse = (new eRede($store))->capture($captureTransaction);

if ($captureResponse->getReturnCode() == '00') {
    printf("Transação capturada com sucesso; tid=%s\n", $captureResponse->getTid());
}
```

### Cancelando uma transação

```php
<?php
// Cancela uma transação usando o TID
$cancelTransaction = (new Transaction(20.99))->setTid('TID123');
$cancelResponse = (new eRede($store))->cancel($cancelTransaction);

if ($cancelResponse->getReturnCode() == '359') {
    printf("Transação cancelada com sucesso; tid=%s\n", $cancelResponse->getTid());
}
```

### Consultando uma transação pelo TID

```php
<?php
$response = (new eRede($store))->get('TID123');

printf("Status: %s\n", $response->getAuthorization()->getStatus());
printf("TID: %s\n", $response->getTid());
printf("NSU: %s\n", $response->getAuthorization()->getNsu());
printf("Código de autorização: %s\n", $response->getAuthorization()->getAuthorizationCode());
```

### Consultando uma transação pela referência

```php
<?php
$response = (new eRede($store))->getByReference('pedido123');

printf("Status: %s\n", $response->getAuthorization()->getStatus());
printf("TID: %s\n", $response->getTid());
```

### Consultando cancelamentos (refunds) de uma transação

```php
<?php
$refundsResponse = (new eRede($store))->getRefunds('TID123');

// Processa os cancelamentos retornados
// Nota: A estrutura de refunds pode variar conforme a resposta da API
```


## Testes

Para executar os testes, você precisa configurar as credenciais no arquivo `.env`:

1. Crie o arquivo `.env` a partir do exemplo:
```bash
make setup-env
# ou
cp env.example .env
```

2. Edite o arquivo `.env` com suas credenciais de sandbox:
```env
REDE_MERCHANT_ID=seu_merchant_id
REDE_MERCHANT_KEY=seu_merchant_key
```

3. Execute os testes:
```bash
# Com Docker
make test

# Localmente
composer test
```

**Nota:** O arquivo `.env` é usado apenas para os testes e está no `.gitignore`.

## Comandos Disponíveis

O projeto inclui um Makefile com comandos úteis:

- `make build` - Constrói as imagens Docker
- `make up` - Inicia os containers
- `make down` - Para os containers
- `make install` - Instala as dependências
- `make test` - Executa os testes
- `make cs-check` - Verifica o código com PHP_CodeSniffer
- `make cs-fix` - Corrige o código automaticamente
- `make phpstan` - Executa análise estática com PHPStan
- `make shell` - Abre um shell no container

## Desenvolvimento

### Estrutura do Projeto

```
rede-auth/
├── src/
│   └── Rede/
│       ├── OAuth/          # Autenticação OAuth 2.0
│       ├── Http/           # Cliente HTTP autenticado
│       ├── Transaction.php # Modelo de transação
│       ├── eRede.php      # Cliente principal do SDK
│       └── ...
├── tests/
│   ├── Unit/              # Testes unitários
│   └── Integration/       # Testes de integração
├── docker/
├── Dockerfile
└── Makefile
```

### Padrões de Código

O projeto segue os princípios de Clean Code e SOLID:

- Single Responsibility Principle
- Dependency Injection
- Interface Segregation
- Testabilidade

## Licença

MIT

