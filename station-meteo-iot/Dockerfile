FROM php:8.2-fpm

# Configuration des sources apt pour éviter les erreurs de connexion
RUN sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list && \
    sed -i 's/security.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list

# Installation des dépendances système avec gestion des erreurs
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip pdo pdo_sqlite intl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY . .

# Installation des dépendances PHP avec gestion des erreurs
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Permissions pour le dossier var
RUN chmod -R 777 var/

# Exposition du port
EXPOSE 9000 