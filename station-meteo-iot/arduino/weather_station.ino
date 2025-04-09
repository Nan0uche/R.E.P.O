/*
 * Station Météo IoT - Code Arduino
 * 
 * Ce code permet de lire les données de capteurs météo et de les envoyer via MQTT
 * 
 * Matériel nécessaire:
 * - Arduino (ESP8266 ou ESP32 recommandé pour la connexion WiFi)
 * - Capteur de température et d'humidité (DHT22 ou BME280)
 * - Capteur de pression (BMP280 ou BME280)
 * - Anémomètre (pour la vitesse du vent)
 * - Girouette (pour la direction du vent)
 * - Pluviomètre
 * 
 * Bibliothèques nécessaires:
 * - PubSubClient (pour MQTT)
 * - WiFiManager (pour la configuration WiFi)
 * - DHT (pour le capteur DHT22)
 * - Adafruit_BME280 (pour le capteur BME280)
 */

#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHT.h>

// Configuration WiFi
const char* ssid = "VOTRE_SSID";
const char* password = "VOTRE_MOT_DE_PASSE";

// Configuration MQTT
const char* mqtt_server = "VOTRE_SERVEUR_MQTT";
const int mqtt_port = 1883;
const char* mqtt_user = "VOTRE_UTILISATEUR_MQTT";
const char* mqtt_password = "VOTRE_MOT_DE_PASSE_MQTT";
const char* mqtt_topic = "weather/station/1"; // Remplacer 1 par l'ID de votre station

// Pins des capteurs
#define DHTPIN D4
#define DHTTYPE DHT22
#define WIND_SPEED_PIN D5
#define WIND_DIRECTION_PIN A0
#define RAIN_PIN D6

// Variables globales
WiFiClient espClient;
PubSubClient client(espClient);
DHT dht(DHTPIN, DHTTYPE);

// Variables pour le pluviomètre
volatile unsigned long rainCount = 0;
unsigned long lastRainCount = 0;
unsigned long lastRainTime = 0;
float rainfall = 0.0;

// Variables pour l'anémomètre
volatile unsigned long windCount = 0;
unsigned long lastWindCount = 0;
unsigned long lastWindTime = 0;
float windSpeed = 0.0;

// Interruption pour le pluviomètre
void rainIRQ() {
  rainCount++;
}

// Interruption pour l'anémomètre
void windIRQ() {
  windCount++;
}

void setup() {
  Serial.begin(115200);
  
  // Initialisation des capteurs
  dht.begin();
  pinMode(WIND_SPEED_PIN, INPUT_PULLUP);
  pinMode(RAIN_PIN, INPUT_PULLUP);
  
  // Configuration des interruptions
  attachInterrupt(digitalPinToInterrupt(RAIN_PIN), rainIRQ, FALLING);
  attachInterrupt(digitalPinToInterrupt(WIND_SPEED_PIN), windIRQ, FALLING);
  
  // Connexion WiFi
  setupWiFi();
  
  // Configuration du client MQTT
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void setupWiFi() {
  delay(10);
  Serial.println();
  Serial.print("Connexion à ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("");
  Serial.println("WiFi connecté");
  Serial.println("Adresse IP: ");
  Serial.println(WiFi.localIP());
}

void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message reçu [");
  Serial.print(topic);
  Serial.print("] ");
  for (int i = 0; i < length; i++) {
    Serial.print((char)payload[i]);
  }
  Serial.println();
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Tentative de connexion MQTT...");
    if (client.connect("ArduinoClient", mqtt_user, mqtt_password)) {
      Serial.println("connecté");
    } else {
      Serial.print("échec, rc=");
      Serial.print(client.state());
      Serial.println(" nouvelle tentative dans 5 secondes");
      delay(5000);
    }
  }
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
  
  // Lecture des capteurs
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  float pressure = 1013.25; // À remplacer par la lecture du capteur BMP280/BME280
  int windDirection = analogRead(WIND_DIRECTION_PIN); // À calibrer selon votre girouette
  
  // Calcul de la vitesse du vent
  unsigned long now = millis();
  if (now - lastWindTime >= 5000) { // Mise à jour toutes les 5 secondes
    windSpeed = (windCount - lastWindCount) * 2.4; // À calibrer selon votre anémomètre
    lastWindCount = windCount;
    lastWindTime = now;
  }
  
  // Calcul des précipitations
  if (now - lastRainTime >= 5000) { // Mise à jour toutes les 5 secondes
    rainfall = (rainCount - lastRainCount) * 0.2794; // À calibrer selon votre pluviomètre
    lastRainCount = rainCount;
    lastRainTime = now;
  }
  
  // Création du message JSON
  StaticJsonDocument<200> doc;
  doc["temperature"] = temperature;
  doc["humidity"] = humidity;
  doc["pressure"] = pressure;
  doc["windSpeed"] = windSpeed;
  doc["windDirection"] = windDirection;
  doc["rainfall"] = rainfall;
  
  char jsonBuffer[200];
  serializeJson(doc, jsonBuffer);
  
  // Envoi des données via MQTT
  Serial.print("Publication: ");
  Serial.println(jsonBuffer);
  client.publish(mqtt_topic, jsonBuffer);
  
  // Attente avant la prochaine lecture
  delay(5000);
} 