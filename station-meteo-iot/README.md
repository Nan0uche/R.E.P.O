# Station Météo IoT

Application web de gestion de stations météo IoT développée avec Symfony 6.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- SQLite3
- Git
- Broker MQTT (comme Mosquitto)

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

9. Démarrer le client MQTT (dans un autre terminal) :
```bash
php bin/console app:mqtt:subscribe
```

## Accès à l'application

- URL : http://localhost:8000
- Compte admin par défaut :
  - Email : admin@station.com
  - Mot de passe : root

## Intégration avec Arduino

### Matériel nécessaire

- Arduino (ESP8266 ou ESP32 recommandé pour la connexion WiFi)
- Capteur de température et d'humidité (DHT22 ou BME280)
- Capteur de pression (BMP280 ou BME280)
- Anémomètre (pour la vitesse du vent)
- Girouette (pour la direction du vent)
- Pluviomètre

### Configuration Arduino

1. Installer les bibliothèques nécessaires dans l'IDE Arduino :
   - PubSubClient (pour MQTT)
   - ArduinoJson
   - DHT (pour le capteur DHT22)
   - Adafruit_BME280 (pour le capteur BME280)

2. Ouvrir le fichier `arduino/weather_station.ino` dans l'IDE Arduino

3. Modifier les paramètres de connexion :
   - SSID et mot de passe WiFi
   - Adresse du serveur MQTT
   - Identifiants MQTT (si nécessaire)
   - Topic MQTT (remplacer l'ID de la station par celui de votre station)

4. Téléverser le code sur votre Arduino

### Format des données MQTT

Les données sont envoyées au format JSON avec la structure suivante :

```json
{
  "temperature": 22.5,
  "humidity": 65.3,
  "pressure": 1013.25,
  "windSpeed": 12.8,
  "windDirection": 180,
  "rainfall": 0.0
}
```

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

- Démarrer le client MQTT :
```bash
php bin/console app:mqtt:subscribe
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

6. Vérifiez que le broker MQTT est en cours d'exécution :
```bash
mosquitto -v
```

## Support

En cas de problème, vérifiez :
1. Que PHP 8.2 ou supérieur est installé
2. Que Composer est installé
3. Que SQLite3 est installé
4. Les permissions des dossiers var/ et public/
5. Que le broker MQTT est en cours d'exécution
6. Les logs du client MQTT 