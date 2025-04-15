#include <MD_Parola.h>
#include <MD_MAX72xx.h>
#include <SPI.h>
#include "Font_Data.h"
#include <Arduino.h>
#include "DHT.h"
#include <WiFi.h>
#include <PubSubClient.h>

// Configuration des broches
#define DHTPIN 15
#define BUTTON_PIN 16
#define PHOTO_PIN 35
#define CLK_PIN 18
#define DATA_PIN 23
#define CS_PIN 21

// Configuration de l'afficheur LED
#define HARDWARE_TYPE MD_MAX72XX::FC16_HW
#define NUM_ZONES 2
#define ZONE_SIZE 4
#define MAX_DEVICES (NUM_ZONES * ZONE_SIZE)
#define ZONE_UPPER 1
#define ZONE_LOWER 0
#define PAUSE_TIME 0
#define SCROLL_SPEED 50
#define SCROLL_LEFT 1

// Configuration WiFi et MQTT
const char* ssid = "yoyo";
const char* password = "lionel123";
const char* mqtt_server = "test.mosquitto.org";
const int mqtt_port = 1883;
const unsigned long publishInterval = 10000;

// Variables globales
MD_Parola display = MD_Parola(HARDWARE_TYPE, CS_PIN, MAX_DEVICES);
DHT dht(DHTPIN, DHT11);
WiFiClient espClient;
PubSubClient mqtt(espClient);

char msgL[1][100];
char* msgH;
bool invertUpperZone;
textEffect_t scrollUpper, scrollLower;
int displayState = 0;
bool lastButtonState = HIGH;
unsigned long lastPublishTime = 0;

// Prototypes des fonctions
void setupDisplay();
void setupWiFi();
void setupMQTT();
void createHString(char* pH, const char* pL);
void sendDataToMQTT(float h, float t, float f);
void displayState1(float h, float t, float f);
void displayState2(float t);
void displayState3(float h);
String getTopicWithMac(const char* baseTopic);

void setup() {
    Serial.begin(115200);
    Serial.println("\n[StationMeteo] Initialisation...");
    
    pinMode(BUTTON_PIN, INPUT_PULLUP);
    dht.begin();
    
    setupDisplay();
    setupWiFi();
    setupMQTT();
    
    // Allouer la mémoire pour msgH
    msgH = (char*)malloc(sizeof(char) * 102);
}

void setupDisplay() {
    invertUpperZone = (HARDWARE_TYPE == MD_MAX72XX::GENERIC_HW || HARDWARE_TYPE == MD_MAX72XX::PAROLA_HW);
    
    if (SCROLL_LEFT) {
        scrollUpper = invertUpperZone ? PA_SCROLL_RIGHT : PA_SCROLL_LEFT;
        scrollLower = PA_SCROLL_LEFT;
    } else {
        scrollUpper = invertUpperZone ? PA_SCROLL_LEFT : PA_SCROLL_RIGHT;
        scrollLower = PA_SCROLL_RIGHT;
    }
    
    display.begin(NUM_ZONES);
    display.setZone(ZONE_LOWER, 0, ZONE_SIZE - 1);
    display.setZone(ZONE_UPPER, ZONE_SIZE, MAX_DEVICES-1);
    display.setFont(BigFont);
    display.setCharSpacing(display.getCharSpacing() * 2);
    
    if (invertUpperZone) {
        display.setZoneEffect(ZONE_UPPER, true, PA_FLIP_UD);
        display.setZoneEffect(ZONE_UPPER, true, PA_FLIP_LR);
    }
}

void setupWiFi() {
    WiFi.begin(ssid, password);
    Serial.print("Connexion au WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connecté!");
    Serial.println("IP: " + WiFi.localIP().toString());
    Serial.println("MAC: " + WiFi.macAddress());
}

void setupMQTT() {
    mqtt.setServer(mqtt_server, mqtt_port);
    while (!mqtt.connected()) {
        Serial.print("Connexion MQTT...");
        String clientId = "StationMeteo-" + String(random(0xffff), HEX);
        if (mqtt.connect(clientId.c_str())) {
            Serial.println("Connecté");
        } else {
            Serial.println("Échec, retry dans 2s");
            delay(2000);
        }
    }
}

void loop() {
    bool buttonState = digitalRead(BUTTON_PIN);
    if (lastButtonState == HIGH && buttonState == LOW) {
        displayState = (displayState + 1) % 3;
        display.displayClear();
        display.displayReset();
        delay(200);
    }
    lastButtonState = buttonState;
    
    // Ajustement automatique de la luminosité
    int photoValue = analogRead(PHOTO_PIN);
    uint8_t intensity = map(photoValue, 0, 4095, 0, 9);
    display.setIntensity(intensity);
    
    // Lecture des capteurs
    float humidity = dht.readHumidity();
    float tempC = dht.readTemperature();
    float tempF = dht.readTemperature(true);
    
    if (!isnan(humidity) && !isnan(tempC) && !isnan(tempF)) {
        // Mise à jour de l'affichage selon l'état
        switch (displayState) {
            case 0: displayState1(humidity, tempC, tempF); break;
            case 1: displayState2(tempC); break;
            case 2: displayState3(humidity); break;
        }
        
        // Publication MQTT périodique
        if (millis() - lastPublishTime > publishInterval) {
            sendDataToMQTT(humidity, tempC, tempF);
            lastPublishTime = millis();
        }
    }
    
    display.displayAnimate();
}

void createHString(char* pH, const char* pL) {
    for (; *pL != '\0'; pL++)
        *pH++ = *pL | 0x80;   // offset character

    *pH = '\0'; // terminate the string
}

void sendDataToMQTT(float h, float t, float f) {
    // Create JSON string
    char payload[100];
    snprintf(payload, sizeof(payload), "{\"temperature\":%.2f,\"humidity\":%.2f}", t, h);
    
    Serial.print("Envoi MQTT - Message: ");
    Serial.println(payload);

    // Publish to a single topic
    if (mqtt.publish(getTopicWithMac("/stationMeteo/data/").c_str(), payload)) {
        Serial.println("Données publiées avec succès !");
    } else {
        Serial.println("Échec de la publication des données !");
    }
}

void displayState1(float h, float t, float f) {
    snprintf(msgL[0], sizeof(msgL[0]), "Humidite: %.0f%% Temp: %.0fC / %.0fF", h, t, f);

    if (display.getZoneStatus(ZONE_LOWER) && display.getZoneStatus(ZONE_UPPER)) {
        createHString(msgH, msgL[0]);

        display.displayClear();

        display.displayZoneText(ZONE_UPPER, msgH, PA_CENTER, SCROLL_SPEED, PAUSE_TIME, scrollUpper, scrollLower);
        display.displayZoneText(ZONE_LOWER, msgL[0], PA_CENTER, SCROLL_SPEED, PAUSE_TIME, scrollUpper, scrollLower);

        display.synchZoneStart();
        delay(10);
    }
}

void displayState2(float t) {
    snprintf(msgL[0], sizeof(msgL[0]), "%.0fC", t);

    display.displayClear();
    createHString(msgH, msgL[0]);
    
    display.displayZoneText(ZONE_UPPER, msgH, PA_CENTER, 0, 0, PA_PRINT, PA_NO_EFFECT);
    display.displayZoneText(ZONE_LOWER, msgL[0], PA_CENTER, 0, 0, PA_PRINT, PA_NO_EFFECT);
    display.synchZoneStart();
    display.displayAnimate();
}

void displayState3(float h) {
    snprintf(msgL[0], sizeof(msgL[0]), "%.0f%%", h);

    display.displayClear();
    createHString(msgH, msgL[0]);
    
    display.displayZoneText(ZONE_UPPER, msgH, PA_CENTER, 0, 0, PA_PRINT, PA_NO_EFFECT);
    display.displayZoneText(ZONE_LOWER, msgL[0], PA_CENTER, 0, 0, PA_PRINT, PA_NO_EFFECT);
    display.synchZoneStart();
    display.displayAnimate();
}

String getTopicWithMac(const char* baseTopic) {
    return String(baseTopic) + WiFi.macAddress();
}
