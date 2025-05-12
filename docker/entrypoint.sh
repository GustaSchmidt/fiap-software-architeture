#!/bin/bash

set -e

# Espera o banco de dados estar pronto (ajuste conforme o nome do serviço no docker-compose)
echo "Aguardando o banco de dados..."
until nc -z -v -w30 db 3306; do
  echo "Aguardando conexão com o banco de dados..."
  sleep 5
done

# Instala as dependências do PHP (se a pasta vendor não existir)
if [ ! -d "vendor" ]; then
  echo "Instalando dependências com Composer..."
  composer install
fi

# Gera a chave da aplicação, se ainda não existir
if [ ! -f ".env" ]; then
  echo "Arquivo .env não encontrado. Abortando."
  exit 1
fi

echo "Gerando APP_KEY..."
php artisan key:generate

# Executa as migrations (remova --seed se não quiser popular)
echo "Executando migrations e seeders..."
php artisan migrate:fresh --seed --force

# Inicia o PHP-FPM
echo "Iniciando o PHP-FPM..."
exec php-fpm
