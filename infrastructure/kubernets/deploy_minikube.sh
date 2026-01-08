#!/bin/bash

# --- Passo 0: Pré-requisitos ---
echo "Verificando se o Minikube está em execução..."
if ! minikube status &> /dev/null; then
    echo "Minikube não está rodando. Iniciando Minikube..."
    minikube start
fi

# Aponta o ambiente Docker para o Minikube
echo "Configurando o ambiente Docker para o Minikube..."
eval $(minikube docker-env)

# Nome da imagem Docker, altere se necessário
IMAGE_NAME="soat-app:latest"

# --- Passo 1: Construir a imagem Docker localmente ---
echo "Construindo a imagem Docker da aplicação: ${IMAGE_NAME}..."
docker build -t ${IMAGE_NAME} ../

if [ $? -ne 0 ]; then
    echo "Erro ao construir a imagem Docker. Abortando."
    exit 1
fi
echo "Imagem Docker construída com sucesso."


# --- Passo 2: Aplicar os manifestos do Kubernetes ---

echo "Aplicando as configurações e secrets..."
kubectl apply -f configmap.yaml
kubectl apply -f secret.yaml

echo "Aplicando a infraestrutura de banco de dados (PostgreSQL)..."
kubectl apply -f postgres-deployment.yaml
kubectl apply -f postgres-service.yaml

echo "Aplicando a infraestrutura de cache (Redis)..."
kubectl apply -f redis-deployment.yaml
kubectl apply -f redis-service.yaml

echo "Aplicando o Deployment e o Service da aplicação..."
kubectl apply -f deployment.yaml
kubectl apply -f service.yaml

echo "Aplicando o Horizontal Pod Autoscaler (HPA)..."
kubectl apply -f hpa.yaml

# --- Passo 3: Aguardar o rollout dos deployments ---
echo "Aguardando o rollout dos deployments..."
kubectl rollout status deployment/postgres-deployment
kubectl rollout status deployment/redis-deployment
kubectl rollout status deployment/soat-app-deployment

echo "Todos os Deployments estão prontos!"

# --- Passo 4: Obter a URL da aplicação ---
echo "Obtendo a URL do serviço da aplicação..."
SERVICE_URL=$(minikube service soat-app-service --url)

if [ -z "$SERVICE_URL" ]; then
    echo "Não foi possível obter a URL do serviço. Tente 'minikube service soat-app-service --url' manualmente."
else
    echo "--------------------------------------------------------"
    echo "Aplicação implantada com sucesso!"
    echo "Acesse a aplicação (API) em: ${SERVICE_URL}"
    echo "--------------------------------------------------------"
fi