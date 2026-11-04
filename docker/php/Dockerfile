
FROM php:8.5-cli-bookworm

# Dependências do sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        intl \
        bcmath \
        zip \
        gd \
        exif \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório do Laravel
WORKDIR /var/www/html

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/laravel-entrypoint

# Porta do servidor Laravel
EXPOSE 8000

# Servidor de desenvolvimento
ENTRYPOINT ["laravel-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
