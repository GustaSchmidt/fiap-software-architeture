# 🍔 FIAP Tech Challenge - Food Delivery API

Projeto desenvolvido como parte do Tech Challenge da Pós-Graduação em Arquitetura de Software da FIAP. O sistema consiste em uma API de gerenciamento de pedidos para uma lanchonete, utilizando arquitetura de microsserviços e práticas modernas de DevOps.

## 🏛️ Arquitetura do Projeto (Monorepo)

Este repositório adota uma estrutura de **Monorepo** para centralizar o código da aplicação, infraestrutura e funções serverless.

```text
.
├── infrastructure/        # IaC (Infrastructure as Code)
│   ├── kubernetes/        # Manifestos K8s (Deployments, Services, HPA)
│   └── terraform/         # Provisionamento AWS (EKS, RDS, S3, Auth)
├── serverless/            # Funções AWS Lambda
│   └── auth-cpf/          # Lambda de Autenticação de Cliente
└── services/              # Microsserviços da Aplicação
    └── core-api/          # API Principal (Laravel/PHP 8.4)

```

---

## 🚀 Como Rodar Localmente

Como o projeto está segregado, você deve acessar a pasta do serviço específico para rodá-lo.

### Pré-requisitos

* Docker e Docker Compose instalados.
* Git instalado.

### Passo a Passo

1. **Clone o repositório:**
```bash
git clone [https://github.com/GustaSchmidt/fiap-software-architeture.git](https://github.com/GustaSchmidt/fiap-software-architeture.git)
cd fiap-software-architeture

```


2. **Acesse a pasta da API Principal:**
```bash
cd services/core-api

```


3. **Configure as Variáveis de Ambiente:**
```bash
cp .env.example .env

```


4. **Suba os containers (App + Banco de Dados):**
```bash
docker-compose up -d

```


5. **Instale as dependências e gere a chave:**
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

```


6. **Acesse a aplicação:**
* API: `http://localhost:8000`
* Documentação Swagger: `http://localhost:8000/api/documentation`



---

## ☁️ Infraestrutura e Deploy (AWS)

A infraestrutura é provisionada automaticamente via **Terraform** e o deploy é feito no **AWS EKS** (Kubernetes) através do GitHub Actions.

### Estrutura de Infra (`/infrastructure`)

* **Terraform:** Gerencia a criação do Cluster EKS, Banco de Dados RDS (Postgres) e Bucket S3 para estado remoto.
* **Kubernetes:** Contém os manifestos de `Deployment`, `Service` (LoadBalancer), `HPA` (Horizontal Pod Autoscaler) e `Secrets`.

### Autenticação Serverless (`/serverless`)

A autenticação do cliente (CPF) é feita através de uma **AWS Lambda** isolada, garantindo escalabilidade independente para o fluxo de login.

---

## 🔄 CI/CD Pipelines

O projeto conta com workflows automatizados no GitHub Actions:

| Workflow | Gatilho | Descrição |
| :--- | :--- | :--- |
| **CI - Main App** | Push/PR em `services/core-api` | 1. Valida o código (Lint/Syntax).<br>2. Sobe banco MySQL temporário.<br>3. Executa testes unitários e de integração (PHPUnit). |
| **CD - Infrastructure** | Push na `main` | 1. **Infra (IaC):** Aplica mudanças do Terraform (RDS, Auth, EKS).<br>2. **App:** Builda e envia imagem Docker ao ECR.<br>3. **Deploy:** Atualiza os manifestos no Cluster EKS.<br>4. **Release:** Gera Release Notes e registra o Deployment no GitHub. |
| **CD - Plan Only** | Pull Request na `main` | Executa apenas o `terraform plan` para validar mudanças de infraestrutura antes do merge. |

---

## 📚 Stack Tecnológica

* **Linguagem:** PHP 8.4 (Laravel 11)
* **Banco de Dados:** MySQL (Local) / PostgreSQL (AWS RDS)
* **Cache:** Redis
* **Serverless:** Node.js (AWS Lambda)
* **Infra:** Terraform, AWS EKS, Docker
* **CI/CD:** GitHub Actions
