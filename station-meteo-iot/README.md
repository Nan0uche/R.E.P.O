# Station Météo IoT

Application web de gestion de stations météo IoT développée avec Symfony 6.

## Prérequis

- Docker Desktop installé sur votre machine
- Git
- Les ports 8000 et 3306 disponibles
- Au moins 2GB d'espace disque libre

## Installation

1. Cloner le projet :
```bash
git clone [https://github.com/Nan0uche/R.E.P.O]
cd station-meteo-iot
```

2. Construire les images Docker :
```bash
docker compose build
```

3. Démarrer les conteneurs :
```bash
docker compose up -d
```

4. Installer les dépendances PHP :
```bash
docker compose exec php composer install
```

5. Créer la base de données :
```bash
docker compose exec php php bin/console doctrine:database:create
```

6. Créer les tables :
```bash
docker compose exec php php bin/console doctrine:schema:create
```

7. Charger les données initiales :
```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

8. Vider le cache :
```bash
docker compose exec php php bin/console cache:clear
```

## Accès à l'application

- URL : http://localhost:8000
- Compte admin par défaut :
  - Email : admin@station.com
  - Mot de passe : admin123

## Commandes utiles

- Voir les logs des conteneurs :
```bash
docker compose logs -f
```

- Arrêter les conteneurs :
```bash
docker compose down
```

- Redémarrer les conteneurs :
```bash
docker compose restart
```

## Mise à jour du projet

Après un `git pull`, exécutez :
```bash
docker compose exec php composer install
docker compose exec php php bin/console cache:clear
```

## Structure du projet

- `/src/Controller` : Contrôleurs de l'application
- `/src/Entity` : Entités Doctrine
- `/src/Form` : Formulaires Symfony
- `/templates` : Templates Twig
- `/public` : Fichiers publics
- `/var` : Fichiers temporaires et base de données

## Support

En cas de problème, vérifiez :
1. Que Docker Desktop est bien en cours d'exécution
2. Que les ports 8000 et 3306 sont disponibles
3. Les logs des conteneurs avec `docker compose logs -f` 