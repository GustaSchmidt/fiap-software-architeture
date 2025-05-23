FROM php:8.3-fpm

ARG user=foodeliveryapi
ARG uid=1000

# Instalar dependências do sistema + extensões do PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    netcat-traditional \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copiar Composer (usando imagem oficial como base)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário de sistema
RUN useradd -u ${uid} -G www-data,root -d /home/${user} -m ${user} \
    && mkdir -p /home/${user}/.composer \
    && chown -R ${user}:${user} /home/${user}

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar configurações personalizadas
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini


# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Trocar para o usuário não root
RUN chown -R ${user}:${user} /var/www
USER ${user}
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]