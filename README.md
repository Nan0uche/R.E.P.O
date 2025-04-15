# Station Météo IoT

Application web de gestion de stations météo IoT développée avec Symfony 6.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- SQLite3
- Git
- Broker MQTT (comme Mosquitto)
- Ngrok (pour exposer le serveur en ligne)

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

## Configuration de la communication Arduino-Site Web

Ce projet utilise MQTT pour la communication entre les stations météo Arduino et le site web, et AJAX pour mettre à jour l'interface en temps réel.

### 1. Installation de Mosquitto (Broker MQTT) sur Windows

1. **Télécharger Mosquitto** :
   - Allez sur [https://mosquitto.org/download/](https://mosquitto.org/download/)
   - Téléchargez la version Windows (par exemple, `mosquitto-2.0.15-install-windows-x64.exe`)

2. **Installer Mosquitto** :
   - Exécutez le fichier d'installation téléchargé
   - Suivez les instructions d'installation (installation par défaut)
   - Assurez-vous que l'option "Install as a service" est cochée pour que Mosquitto démarre automatiquement

3. **Configurer Mosquitto** :
   - Allez dans le dossier d'installation (généralement `C:\Program Files\mosquitto`)
   - Ouvrez le fichier `mosquitto.conf` avec un éditeur de texte (comme Notepad)
   - Ajoutez les lignes suivantes pour permettre les connexions externes :
     ```
     listener 1883
     allow_anonymous true
     ```
   - Enregistrez le fichier

4. **Redémarrer le service Mosquitto** :
   - Ouvrez les Services Windows (tapez `services.msc` dans le menu Démarrer)
   - Trouvez le service "Mosquitto Broker"
   - Cliquez droit et sélectionnez "Redémarrer"

5. **Vérifier l'installation** :
   - Ouvrez une invite de commande (cmd) en tant qu'administrateur
   - Tapez `mosquitto -v` pour vérifier que Mosquitto est installé correctement

6. **Tester Mosquitto** :
   - Ouvrez deux invites de commande
   - Dans la première, exécutez :
     ```
     mosquitto_sub -h localhost -t "test" -v
     ```
   - Dans la seconde, exécutez :
     ```
     mosquitto_pub -h localhost -t "test" -m "Hello"
     ```
   - Vous devriez voir "Hello" dans la première invite

### 2. Installation et configuration de Ngrok sur Windows

1. **Télécharger Ngrok** :
   - Allez sur [https://ngrok.com/download](https://ngrok.com/download)
   - Téléchargez la version Windows
   - Extrayez le fichier dans un dossier (par exemple, `C:\ngrok`)

2. **Configurer Ngrok** :
   - Créez un compte gratuit sur [https://dashboard.ngrok.com/signup](https://dashboard.ngrok.com/signup)
   - Récupérez votre token d'authentification
   - Ouvrez une invite de commande
   - Naviguez vers le dossier où vous avez extrait Ngrok :
     ```
     cd C:\ngrok
     ```
   - Configurez votre token :
     ```
     ngrok config add-authtoken VOTRE_TOKEN
     ```

3. **Exposer le port MQTT** :
   - Ouvrez une invite de commande
   - Naviguez vers le dossier Ngrok :
     ```
     cd C:\ngrok
     ```
   - Exécutez :
     ```
     ngrok tcp 1883
     ```
   - Notez l'adresse et le port fournis (par exemple, `0.tcp.ngrok.io:12345`)

4. **Exposer le serveur Symfony** :
   - Ouvrez une autre invite de commande
   - Naviguez vers le dossier Ngrok :
     ```
     cd C:\ngrok
     ```
   - Exécutez :
     ```
     ngrok http 8000
     ```
   - Notez l'URL HTTP fournie (par exemple, `https://abc123.ngrok.io`)

5. **Garder Ngrok en cours d'exécution** :
   - Les commandes Ngrok doivent rester en cours d'exécution pour que les tunnels restent actifs
   - Vous pouvez les exécuter dans des fenêtres d'invite de commande séparées
   - Si vous fermez une fenêtre, le tunnel correspondant sera fermé

### 3. Configuration du service MQTT dans Symfony

Le service MQTT est déjà configuré dans le projet. Si vous devez modifier les paramètres de connexion, éditez le fichier `src/Service/MqttService.php` :

```php
public function __construct(
    LoggerInterface $logger,
    EntityManagerInterface $entityManager,
    string $brokerHost = '0.tcp.ngrok.io', // Adresse Ngrok
    int $brokerPort = 12345, // Port Ngrok
    string $brokerUsername = '',
    string $brokerPassword = ''
) {
    // ...
}
```

### 4. Configuration des stations Arduino

1. **Installer les bibliothèques Arduino** :
   - Ouvrez l'IDE Arduino
   - Allez dans Outils > Gérer les bibliothèques
   - Installez les bibliothèques suivantes :
     - PubSubClient
     - ArduinoJson
     - DHT (pour le capteur DHT22)
     - Adafruit_BME280 (pour le capteur BME280)

2. **Configurer le code Arduino** :
   - Ouvrez le fichier `arduino/weather_station.ino`
   - Modifiez les paramètres de connexion :
     ```cpp
     // Configuration WiFi
     const char* ssid = "VOTRE_SSID";
     const char* password = "VOTRE_MOT_DE_PASSE";
     
     // Configuration MQTT
     const char* mqtt_server = "0.tcp.ngrok.io"; // Adresse Ngrok
     const int mqtt_port = 12345; // Port Ngrok
     const char* mqtt_user = ""; // Laissez vide si allow_anonymous est true
     const char* mqtt_password = ""; // Laissez vide si allow_anonymous est true
     const char* mqtt_topic = "weather/station/1"; // Remplacer 1 par l'ID de votre station
     ```

3. **Téléverser le code sur l'Arduino** :
   - Connectez votre Arduino à l'ordinateur
   - Sélectionnez le bon port dans l'IDE Arduino
   - Cliquez sur le bouton "Téléverser"

### 5. Démarrage du système

1. **Démarrer le serveur Symfony** :
   - Ouvrez une invite de commande
   - Naviguez vers le dossier du projet :
     ```
     cd chemin\vers\station-meteo-iot
     ```
   - Exécutez :
     ```
     symfony server:start
     ```

2. **Démarrer Ngrok** :
   - Ouvrez deux nouvelles invites de commande
   - Dans la première, naviguez vers le dossier Ngrok et exposez le serveur :
     ```
     cd C:\ngrok
     ngrok http 8000
     ```
   - Dans la seconde, exposez le port MQTT :
     ```
     cd C:\ngrok
     ngrok tcp 1883
     ```
   - Notez les adresses et ports fournis

3. **Démarrer le client MQTT** :
   - Ouvrez une nouvelle invite de commande
   - Naviguez vers le dossier du projet :
     ```
     cd chemin\vers\station-meteo-iot
     ```
   - Exécutez :
     ```
     php bin/console app:mqtt:subscribe
     ```

4. **Accéder à l'application** :
   - Ouvrez un navigateur web
   - Accédez à l'URL Ngrok de votre application (par exemple, `https://abc123.ngrok.io`)

### 6. Fonctionnement du système

1. **Envoi des données** :
   - Les stations Arduino lisent les capteurs toutes les 5 secondes
   - Les données sont envoyées au broker MQTT via le tunnel Ngrok
   - Le format des données est JSON :
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

2. **Réception des données** :
   - Le client MQTT dans Symfony s'abonne au topic `weather/station/#`
   - Lorsqu'une donnée est reçue, elle est enregistrée dans la base de données
   - L'ID de la station est extrait du topic (format: `weather/station/{id}`)

3. **Affichage des données** :
   - La page de détail d'une station météo utilise AJAX pour récupérer les données en temps réel
   - Les données sont mises à jour toutes les 5 secondes
   - Un graphique affiche l'historique des données

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
   - Adresse du serveur MQTT (adresse Ngrok)
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

- Exposer le serveur avec Ngrok :
```bash
ngrok http 8000
```

- Exposer le port MQTT avec Ngrok :
```bash
ngrok tcp 1883
```

## Dépannage sur Windows

Si vous rencontrez des problèmes :

1. **Problème avec Mosquitto** :
   - Vérifiez que le service Mosquitto est en cours d'exécution dans les Services Windows
   - Vérifiez que le port 1883 n'est pas bloqué par le pare-feu Windows
   - Essayez de redémarrer le service Mosquitto

2. **Problème avec Ngrok** :
   - Vérifiez que Ngrok est correctement configuré avec votre token
   - Vérifiez que les commandes Ngrok sont toujours en cours d'exécution
   - Si les tunnels expirent, redémarrez les commandes Ngrok

3. **Problème avec le client MQTT** :
   - Vérifiez que vous utilisez la bonne adresse et le bon port dans le service MQTT
   - Vérifiez les logs du client MQTT dans Symfony
   - Essayez de redémarrer le client MQTT

4. **Problème avec l'Arduino** :
   - Vérifiez que l'Arduino est correctement connecté à Internet
   - Vérifiez que les paramètres de connexion MQTT sont corrects
   - Vérifiez les messages de débogage dans le moniteur série de l'IDE Arduino

5. **Problème avec Symfony** :
   - Vérifiez que le serveur Symfony est en cours d'exécution
   - Vérifiez les logs de Symfony dans `var/log/dev.log`
   - Essayez de vider le cache : `php bin/console cache:clear`

6. **Problème avec la base de données** :
   - Vérifiez que la base de données SQLite est créée dans `var/data.db`
   - Vérifiez les permissions du dossier `var`
   - Essayez de réinitialiser la base de données :
     ```
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
5. Que le broker MQTT est en cours d'exécution
6. Que Ngrok est en cours d'exécution
7. Les logs du client MQTT 