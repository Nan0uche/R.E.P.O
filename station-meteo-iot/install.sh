#!/bin/bash

# Arrêter les conteneurs existants
docker compose down

# Supprimer les images existantes
docker system prune -af

# Construire et démarrer les conteneurs
docker compose up -d --build

# Attendre que les conteneurs soient prêts
echo "Attente du démarrage des conteneurs..."
sleep 10

# Créer la base de données et mettre à jour le schéma
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:schema:update --force

# Charger les données initiales
docker compose exec php php bin/console doctrine:fixtures:load

# Vider le cache
docker compose exec php php bin/console cache:clear

echo "Installation terminée !"
echo "Vous pouvez accéder à l'application à l'adresse : http://localhost:8000"
echo "Compte admin : admin@station.com / root" 