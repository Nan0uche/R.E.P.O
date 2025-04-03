FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo_sqlite pdo zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Installation des dépendances avec les dépendances de développement
RUN composer install --no-interaction --optimize-autoloader

RUN chmod -R 777 var/

EXPOSE 9000 