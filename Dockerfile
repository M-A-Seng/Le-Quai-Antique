# -------------------------------
# Build
FROM php:8.5.2-cli AS build

# Dossier temporaire pour construire l'image
WORKDIR /app

# Dépendances système (PHP + Node)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libonig-dev \
    libssl-dev \
    nodejs \
    npm \
    && docker-php-ext-install zip pdo pdo_pgsql mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer depuis l'image officielle
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Dépendances PHP (cache Docker optimisé)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-progress

# Dépendances JS
COPY package.json package-lock.json ./
RUN npm ci

# Copier le code de l'application
COPY . .

# Construire les assets JS
RUN npm run build

# -------------------------------
# Image
FROM php:8.5.2-apache AS production

WORKDIR /var/www/html

# Extensions PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
        zip \
        pdo \
        pdo_pgsql \
        mbstring \
    && docker-php-ext-enable opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# config opcache pour performance php
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.revalidate_freq=2"; \
    echo "opcache.validate_timestamps=1"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# opcache dans public/ au lieu de root.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Activer module rewrite pour Apache
RUN a2enmod rewrite

# Copier l'application depuis le build
COPY --from=build /app /var/www/html

# autorisations
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# port HTTP standard
EXPOSE 80

# Démarrage Apache
CMD ["apache2-foreground"]