# Station Météo IoT

Application web de gestion de stations météo IoT développée avec Symfony 6.

## Prérequis

- Docker Desktop installé sur votre machine
- Git
- Les ports 8000 et 80 disponibles

## Installation

### Option 1 : Installation automatique (recommandée)

#### Pour Windows :

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd station-meteo-iot
```

2. Exécuter le script d'installation :
```bash
install.bat
```

#### Pour Linux/Mac :

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd station-meteo-iot
```

2. Rendre le script d'installation exécutable :
```bash
chmod +x install.sh
```

3. Exécuter le script d'installation :
```bash
./install.sh
```

### Option 2 : Installation manuelle

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd station-meteo-iot
```

2. Construire et démarrer les conteneurs :
```bash
docker compose up -d --build
```

3. Créer la base de données et mettre à jour le schéma :
```bash
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:schema:update --force
```

4. Charger les données initiales :
```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

5. Vider le cache :
```bash
docker compose exec php php bin/console cache:clear
```

## Accès à l'application

- URL : http://localhost:8000
- Compte admin par défaut :
  - Email : admin@station.com
  - Mot de passe : root

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

## Dépannage

Si vous rencontrez des problèmes :

1. Arrêtez tous les conteneurs :
```bash
docker compose down
```

2. Supprimez les images :
```bash
docker system prune -af
```

3. Reconstruisez l'image :
```bash
docker compose build --no-cache
```

4. Redémarrez les conteneurs :
```bash
docker compose up -d
```

5. Réinitialisez la base de données :
```bash
docker compose exec php php bin/console doctrine:database:drop --force
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:schema:update --force
docker compose exec php php bin/console doctrine:fixtures:load
```

## Support

En cas de problème, vérifiez :
1. Que Docker Desktop est bien en cours d'exécution
2. Que les ports 8000 et 80 sont disponibles
3. Les logs des conteneurs avec `docker compose logs -f`
4. Les permissions des dossiers var/ et public/ si vous avez des problèmes d'accès 