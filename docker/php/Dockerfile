FROM php:8.5.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Installer php composer directement dans l'image
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer autoload directement dans l'image
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000