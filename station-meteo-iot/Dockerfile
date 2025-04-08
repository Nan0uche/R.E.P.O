FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP
RUN docker-php-ext-install zip pdo pdo_sqlite intl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Création d'un utilisateur non-root
RUN useradd -m -u 1000 appuser

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY --chown=appuser:appuser . .

# Installation des dépendances PHP
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Permissions pour le dossier var
RUN chmod -R 777 var/ && \
    chown -R appuser:appuser var/

# Configuration du cache Symfony
RUN mkdir -p var/cache && \
    chmod -R 777 var/cache && \
    chown -R appuser:appuser var/cache

# Changement d'utilisateur
USER appuser

# Exposition du port
EXPOSE 9000 