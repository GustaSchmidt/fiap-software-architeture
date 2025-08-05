# fiap-software-architeture

# SOAT Tech Challenge - Fast Food (FASE 2)

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

## ⚛️ Rodando no minikube (o mais proximo de prod)

### 1. Arquitetura da Solução

A arquitetura da solução é baseada em microsserviços rodando em um cluster Kubernetes. Os principais componentes são:

  * **Backend da Aplicação (`soat-app`)**: Um `Deployment` de dois pods que executam a aplicação Laravel. Ele se conecta ao banco de dados e ao cache. A escalabilidade é gerenciada por um `HorizontalPodAutoscaler` (HPA) que ajusta o número de pods com base no uso da CPU para lidar com a demanda, resolvendo possíveis problemas de performance no totem de autoatendimento.
  * **Serviço de Banco de Dados (`postgres-deployment`)**: Um `Deployment` de um único pod que executa um banco de dados PostgreSQL. Os dados são persistidos através de um `Volume` temporário (para o ambiente de desenvolvimento). As credenciais de acesso são fornecidas de forma segura através de um `Secret` do Kubernetes.
  * **Serviço de Cache (`redis-deployment`)**: Um `Deployment` de um único pod que executa uma instância do Redis para gerenciamento de cache e sessões da aplicação.
  * **Serviços (`soat-app-service`, `postgres-service`, `redis-service`)**: Objetos `Service` do Kubernetes que gerenciam o acesso e a comunicação entre os pods. O `soat-app-service` expõe a aplicação para o mundo exterior.
  * **Configurações e Segredos**: Valores sensíveis, como senhas, são armazenados em um `Secret` (`soat-secrets`). Já configurações não sensíveis, como nomes de usuários e de banco de dados, são armazenadas em um `ConfigMap` (`soat-config`).

### 2. Pré-requisitos

Para executar o projeto, você precisa ter as seguintes ferramentas instaladas:

  * **Docker**: Para construir a imagem da aplicação.
  * **Minikube**: Para rodar o cluster Kubernetes localmente.
  * **kubectl**: A ferramenta de linha de comando do Kubernetes.

### 3. Guia de Execução

Siga os passos abaixo para implantar o projeto no Minikube.

#### Passo A: Preparar os Arquivos de Configuração

Certifique-se de que os seguintes arquivos de manifesto Kubernetes (`.yaml`) estão no diretório  `kubernets\` e configurados corretamente:

  * `configmap.yaml`
  * `secret.yaml`
  * `deployment.yaml`
  * `service.yaml`
  * `hpa.yaml`
  * `postgres-deployment.yaml`
  * `postgres-service.yaml`
  * `redis-deployment.yaml`
  * `redis-service.yaml`

Seus arquivos `configmap.yaml` e `secret.yaml` devem ter as seguintes credenciais, que correspondem ao `env.example` da aplicação:

  * `db_name`: `laravel`
  * `db_user`: `root`
  * `db_password`: `root`

#### Passo B: Script de Implantação

Utilize o script `deploy_minikube.sh` para automatizar o processo de construção e implantação da aplicação. O script irá:

1.  Verificar e iniciar o Minikube.
2.  Construir a imagem Docker da aplicação no ambiente do Minikube.
3.  Aplicar todos os manifestos do Kubernetes.
4.  Aguardar o rollout de todos os deployments.
5.  Iniciar o port-forward para expor a aplicação.

Execute o script com o seguinte comando:

```bash
./deploy_minikube.sh
```

#### Passo C: Acessar a Aplicação

Depois que o script for concluido pode utilizar a URL de acesso disponibilizada no log de saida do script.

Caso o seu ambiente seja o codespace utilize o `kubectl port-forward service/soat-app-service 8000:8000` para realizar o portfoward e expor a porta da aplicação!

  * **URL da API**: `http://localhost:8000`

A documentação do Swagger da API estará disponível em:

  * **Swagger UI**: `http://localhost:8000/api/documentation`

## Outros documentos:

- [Arquitetura geral](docs/architeture.md)
- [Explicações do checkout](docs/checkout_docs.md)
- [Como a aplicação usa o DB](docs/data_persistece.md)
- [Domain-Driven Design (DDD): Um Resumo](docs/ddd.md)
- [Detalhes do Kubernets](docs/k8_architeture.md)
