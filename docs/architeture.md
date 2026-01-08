# 🏗️ Arquitetura de Software - Food Delivery

Este documento descreve a arquitetura de alto nível do sistema de Food Delivery, detalhando seus componentes, decisões de design e a estrutura de organização do código (Monorepo).

## 🔭 Visão Geral

O sistema foi arquitetado seguindo princípios **Cloud-Native**, utilizando containers orquestrados via Kubernetes (EKS), funções Serverless para autenticação isolada e Banco de Dados gerenciado (RDS), tudo provisionado via Infraestrutura como Código (Terraform).

### Diagrama de Container (C4 Level 2)

```mermaid
graph TD
    User((Cliente))
    ext_mp[Mercado Pago API]

    subgraph "AWS Cloud (us-east-1)"
        subgraph "Serverless Layer"
            LambdaAuth[Lambda: Auth CPF]
        end

        subgraph "Kubernetes Cluster (EKS)"
            LB[AWS Load Balancer]
            App[Pod: Core API (Laravel)]
            HPA[Horizontal Pod Autoscaler]
        end

        subgraph "Data Layer"
            RDS[(Amazon RDS: PostgreSQL)]
            ElastiCache[(Redis Cache)]
        end
        
        S3[S3 Bucket: Terraform State]
    end

    %% Fluxos
    User -->|HTTPS/Traffic| LB
    LB -->|Routing| App
    App -->|Read/Write| RDS
    App -->|Cache| ElastiCache
    App -->|Payment Webhooks| ext_mp
    
    %% Auth Flow
    User -.->|Login/Identify| LambdaAuth
    LambdaAuth -.->|Return Token/JWT| User
    
    %% Auto Scaling
    HPA -.->|Monitor CPU/RAM| App