FROM php:8.5.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    curl \
    && docker-php-ext-install \
        pdo_pgsql \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Installer php composer directement dans l'image
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./

# Installer autoload directement dans l'image
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000