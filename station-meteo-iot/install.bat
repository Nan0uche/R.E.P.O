@echo off
echo Installation de Station Meteo IoT...

echo Arrêt des conteneurs existants...
docker compose down

echo Suppression des images existantes...
docker system prune -af

echo Construction et démarrage des conteneurs...
docker compose up -d --build

echo Attente du démarrage des conteneurs...
timeout /t 10 /nobreak

echo Création de la base de données et mise à jour du schéma...
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:schema:update --force

echo Chargement des données initiales...
docker compose exec php php bin/console doctrine:fixtures:load

echo Vidage du cache...
docker compose exec php php bin/console cache:clear

echo Installation terminée !
echo Vous pouvez accéder à l'application à l'adresse : http://localhost:8000
echo Compte admin : admin@station.com / root

pause 