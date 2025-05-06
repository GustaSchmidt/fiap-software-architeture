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

- `POST /clientes` – Cadastro de cliente
- `GET /clientes/:cpf` – Identificação de cliente via CPF
- `POST /produtos` – Cadastro de produto
- `PUT /produtos/:id` – Edição de produto
- `DELETE /produtos/:id` – Remoção de produto
- `GET /produtos?categoria=...` – Listagem de produtos por categoria
- `POST /checkout` – Finalização de pedido (envio à fila)
- `GET /pedidos` – Listagem de pedidos

## 🚀 Como Executar o Projeto Localmente

### Pré-requisitos
- Docker
- Docker Compose

### Passos

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/seu-repo.git
cd seu-repo

# Crie o Arquivo .env
# Atualize as variáveis de ambiente do arquivo .env de acordo com seu ambiente
cp .env.example .env

# Suba os containers do projeto
docker-compose up --build

# Acessar o container
docker-compose exec app bash

# Instalar as dependências do projeto
composer install

# Gerar a key do projeto Laravel
php artisan key:generate

# Execultar as migrations
php artisan migrate:fresh --seed
```

Acessar o projeto localmente
[http://localhost:8989](http://localhost:8989)