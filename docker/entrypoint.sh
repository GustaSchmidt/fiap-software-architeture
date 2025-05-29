#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Instalando dependencias ---
echo "ℹ️ Instalando dependências do Composer."
composer install --no-dev --optimize-autoloader --no-interaction --no-progress
echo "✅ Dependências do Composer instaladas."

# --- Configuração Inicial e Verificações ---
echo "🚀 Iniciando app..."

# 1. Chave da Aplicação (APP_KEY)
if [ -z "$APP_KEY" ]; then
  echo "❌ Erro: A variável de ambiente APP_KEY não está definida."
  echo "Por favor, configure APP_KEY nas secrets do seu serviço Koyeb."
  exit 1
fi
echo "🔑 APP_KEY detectada."

# 2. Aguardar o Banco de Dados
echo "⏳ Aguardando o banco de dados com Artisan..."

timeout_seconds=60
start_time=$(date +%s)

until php artisan db:test; do
  current_time=$(date +%s)
  elapsed_time=$((current_time - start_time))
  
  if [ "$elapsed_time" -ge "$timeout_seconds" ]; then
    echo "❌ Erro: Timeout ao aguardar o banco de dados após ${timeout_seconds} segundos."
    exit 1
  fi
  
  echo "🔁 Tentando novamente em 5 segundos..."
  sleep 5
done

echo "✅ Banco de dados conectado com sucesso!"



# 4. Configuração do Laravel (Otimizações)
echo "⚙️  Aplicando otimizações do Laravel (config, route, view)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Otimizações do Laravel aplicadas."


# 5. Execução de Migrations
echo "🔄 Executando migrations..."

if [ "$APP_ENV" = "prd" ]; then
  echo "🚀 Ambiente de Produção detectado (${APP_ENV}). Aplicando apenas migrations pendentes..."
  php artisan migrate --force
elif [ "$APP_ENV" = "dev" ]; then
  echo "🔧 Ambiente de Desenvolvimento detectado (${APP_ENV}). Limpando e populando o banco de dados (migrate:fresh --seed)..."
  php artisan migrate:fresh --seed --force
else
  echo "ℹ️ Ambiente não especificado ou desconhecido (${APP_ENV}). Aplicando apenas migrations pendentes (comportamento padrão)..."
  php artisan migrate --force
fi
echo "✅ Migrations concluídas."

# Criando chave de API para usar no teste
if [ "$APP_ENV" = "dev" ]; then
  echo "🔑 Gerando chave de API de teste..."
  php artisan apikey:create "Chave de Teste" --role=admin --client-id=1 || {
    echo "⚠️  Falha ao criar a chave de API de teste (talvez já exista)."
  }
fi

# --- Inicialização do Servidor ---

# Inicia o PHP-FPM
# 'exec' substitui o processo do shell pelo php-fpm
echo "🚀 Iniciando server artisan"
php artisan serve --host=0.0.0.0 --port=8000