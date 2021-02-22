# Extensão Integrai para Magento 2
Módulo para integrar sua loja com a Integrai, integrando com diversos parceiros com apenas 1 plugin.

## Requisitos

- [Magento Community](https://magento.com/products/community-edition) 2.x
- [PHP](http://php.net) >= 5.6.x
- Cron

## Instalação

### Manual
1. Baixe a ultima versão [aqui](https://codeload.github.com/integrai/magento2/zip/master)
2. Criar a seguinte estrutura de pastas `app/code/Integrai/Core` na raiz da sua instalação.
3. Descompacte o arquivo baixado e copie as pastas para dentro do diretório criado acima.
4. Digite os seguintes comandos, no terminal, para habilitar o módulo:  
```bash
php bin/magento module:enable Integrai_Core --clear-static-content
php bin/magento setup:upgrade
```

### Instalar usando o [composer](https://getcomposer.org/)

1. Entre na pasta raíz da sua instalação
2. Digite o seguinte comando:
```bash
composer require integrai/magento2
```
3. Digite os seguintes comandos, no terminal, para habilitar o módulo:
```bash
php bin/magento module:enable Integrai_Core --clear-static-content
php bin/magento setup:upgrade
```

## Configuração
1. Acesse o painel administrativo da sua loja
2. Vá em `Lojas (Store) > Configuração (Configuration) > Integrai > Configurações de integração`
3. Informe sua **API Key** e sua **Secret Key**, que são informadas [aqui](https://manage.integrai.com.br/settings/account)
4. Salve as configurações
5. Em `Lojas (Store) > Configuração (Configuration) > Clientes (Customers) > Configuração do cliente (Customer Configuration)`, altere o valor dos campos:
    * Em `Opções de criação de conta (Create New Account Options)` altere o `Exibir Tax/Vat (Show VAT Number on Storefront)` com valor `Sim (Yes)`
    * Em `Opções de nome e endereço (Name and Address Options)` trocar o `Número de linhas em um endereço de rua (Number of Lines in a Street Address)` com valor `4`
    * `Mostrar data de aniversário (Show Date of Birth)` com valor `Requerido (Required)`
    * `Mostrar número documento (Show Tax/VAT Number)` com valor `Requerido (Required)`
    * `Mostrar gênero (Show Gender)` com valor `Requerido (Required)`
6. Salve as configuraçõesx
