# fiap-software-architeture

# SOAT Tech Challenge - Fast Food (FASE 1)

## 🧾 Descrição do Projeto

Este projeto tem como objetivo desenvolver o backend de um sistema de autoatendimento para uma lanchonete em expansão, buscando resolver os problemas de controle de pedidos e melhorar a experiência dos clientes. O sistema permitirá ao cliente montar seu combo, realizar o pagamento via QRCode do Mercado Pago e acompanhar o status do pedido em tempo real.

## 🎯 Funcionalidades

### Cliente
- Cadastro com nome, e-mail e CPF (opcional)
- Montagem de pedido com as etapas:
  - Lanche
  - Acompanhamento
  - Bebida
  - Sobremesa
- Pagamento via QRCode (Mercado Pago)
- Acompanhamento do status do pedido:
  - Recebido
  - Em preparação
  - Pronto
  - Finalizado

### Administrador
- Gerenciamento de clientes
- Cadastro, edição e remoção de produtos
- Organização de produtos por categorias fixas:
  - Lanche
  - Acompanhamento
  - Bebida
  - Sobremesa
- Acompanhamento de pedidos e seus tempos de espera

## 🏗️ Tecnologias e Arquitetura

- Backend monolítico
- Arquitetura Hexagonal
- APIs RESTful documentadas via Swagger
- Banco de dados à escolha (com controle de fila de pedidos)
- Docker + Docker Compose

## 📦 Endpoints da API

Endpoints documentados em swagger no /public/swagger.json

## 🚀 Como Executar o Projeto Localmente

### Pré-requisitos
- Docker
- Docker Compose

### Passos

```bash
# Clone o repositório
git clone https://github.com/GustaSchmidt/fiap-software-architeture.git
cd fiap-software-architeture

# Crie o Arquivo .env
# Atualize as variáveis de ambiente do arquivo .env de acordo com seu ambiente
cp .env.example .env

# Suba os containers do projeto
docker compose up --build

# Para acessar o container pra casos de debug
docker compose exec app bash
```

## 🚀 Cagou com o DB e precisa reiniciar? (so para ambiente de DEV)

```bash
# Para acessar o container pra casos de debug
docker compose exec app bash

# Limpar db
php artisan migrate:fresh --seed --force
```

Acessar o projeto localmente
[http://localhost:8989](http://localhost:8989)

## 🚀 APIKey como usar essa bagaça
### Comando Artisan: `apikey:create`

Este comando Artisan permite criar uma nova API Key no sistema com opções personalizadas como nome, role, ID do cliente/loja, e status (ativa ou inativa).

#### Uso

```bash
php artisan apikey:create "Nome da Integração" [opções]
```

**Argumentos obrigatórios**
```bash
name
Nome legível para a API Key.
Exemplo: "Integração Serviço X"
```

**Opções**
```bash
--role ou -r
Define a role associada à API Key.
Exemplo: --role=admin

--client-id ou -c
ID inteiro do cliente ou loja associado à role.
Exemplo: --client-id=123

--inactive
Cria a chave como inativa (por padrão, a chave é criada como ativa).
```