# Station Météo IoT

Application web de gestion de stations météo IoT développée avec Symfony 6.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- SQLite3
- Git

## Installation

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd station-meteo-iot
```

2. Installer les dépendances PHP :
```bash
composer install
```

3. Créer le fichier de configuration local :
```bash
cp .env.local.example .env.local
```

4. Créer la base de données :
```bash
php bin/console doctrine:database:create
```

5. Créer les tables :
```bash
php bin/console doctrine:schema:update --force
```

6. Charger les données initiales :
```bash
php bin/console doctrine:fixtures:load
```

7. Vider le cache :
```bash
php bin/console cache:clear
```

8. Démarrer le serveur de développement :
```bash
symfony server:start
```

## Accès à l'application

- URL : http://localhost:8000
- Compte admin par défaut :
  - Email : admin@station.com
  - Mot de passe : root

## Commandes utiles

- Démarrer le serveur de développement :
```bash
symfony server:start
```

- Arrêter le serveur de développement :
```bash
symfony server:stop
```

- Vider le cache :
```bash
php bin/console cache:clear
```

## Dépannage

Si vous rencontrez des problèmes :

1. Vérifiez que PHP 8.2 ou supérieur est installé :
```bash
php -v
```

2. Vérifiez que Composer est installé :
```bash
composer -V
```

3. Vérifiez que SQLite3 est installé :
```bash
sqlite3 --version
```

4. Vérifiez les permissions des dossiers :
```bash
chmod -R 777 var/
```

5. Réinitialisez la base de données :
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load
```

## Support

En cas de problème, vérifiez :
1. Que PHP 8.2 ou supérieur est installé
2. Que Composer est installé
3. Que SQLite3 est installé
4. Les permissions des dossiers var/ et public/ 