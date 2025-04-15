<?php

namespace App\Service;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Entity\User;

class MqttService
{
    private $client;
    private $broker;
    private $port;
    private $clientId;
    private $messageCallback;
    private $logger;
    private $entityManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        string $broker = 'test.mosquitto.org',
        int $port = 1883
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->broker = $broker;
        $this->port = $port;
        $this->clientId = 'station-meteo-' . uniqid();
    }

    public function connect(): void
    {
        try {
            $connectionSettings = new ConnectionSettings();
            $connectionSettings
                ->setConnectTimeout(60)
                ->setKeepAliveInterval(60);

            $this->client = new MqttClient($this->broker, $this->port, $this->clientId);
            $this->client->connect($connectionSettings);
            $this->logger->info('Connected to MQTT broker');
        } catch (\Exception $e) {
            $this->logger->error('Failed to connect to MQTT broker: ' . $e->getMessage());
            throw $e;
        }
    }

    public function onMessage(callable $callback): void
    {
        $this->messageCallback = $callback;
    }

    public function subscribe(string $topic): void
    {
        try {
            if (!$this->client) {
                $this->connect();
            }

            $this->client->subscribe($topic, function ($topic, $message) {
                // Convertir le message en chaîne de caractères
                $payload = $message;
                if (is_object($message) && method_exists($message, 'getPayload')) {
                    $payload = $message->getPayload();
                }

                // Appeler le callback avec le message
                if ($this->messageCallback) {
                    call_user_func($this->messageCallback, $topic, $payload);
                }

                // Logger le message reçu pour le débogage
                $this->logger->info("Message reçu sur $topic: " . print_r($payload, true));
                
                // Gérer le message pour la base de données
                $this->handleMessage($topic, $payload);
            }, 0);

            $this->logger->info('Subscribed to topic: ' . $topic);
        } catch (\Exception $e) {
            $this->logger->error('Failed to subscribe to topic: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleMessage(string $topic, string $message): void
    {
        try {
            if (!$this->entityManager->isOpen()) {
                $this->logger->warning('EntityManager is closed. Reopening it.');
                $this->entityManager = $this->entityManager->getConnection()->getEntityManager();
            }

            $this->logger->info('Message reçu: ' . $message . ' sur topic: ' . $topic);

            // Extract information from the topic
            $topicParts = explode('/', $topic);
            if (count($topicParts) !== 4) {
                $this->logger->error('Format de topic invalide: ' . $topic);
                return;
            }

            $type = $topicParts[2];
            $macAddress = $topicParts[3];

            // Find the weather station
            $station = $this->entityManager->getRepository(WeatherStation::class)
                ->findOneBy(['macAddress' => $macAddress]);

            // Si la station n'existe pas, on ignore les données
            if (!$station) {
                $this->logger->warning('Station inconnue, données ignorées. MAC: ' . $macAddress);
                return;
            }

            // Parse the JSON message
            $data = json_decode($message, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Erreur de parsing JSON: ' . json_last_error_msg());
                return;
            }

            // Create new weather data entry
            $weatherData = new WeatherData();
            $weatherData->setStation($station);
            $weatherData->setType($type);
            $weatherData->setTimestamp(new \DateTime());
            
            // Store the temperature and humidity directly in their respective fields
            if (isset($data['temperature'])) {
                $weatherData->setTemperature((float) $data['temperature']);
            }
            if (isset($data['humidity'])) {
                $weatherData->setHumidity((float) $data['humidity']);
            }

            // Store the raw JSON data in the value field for reference
            $weatherData->setValue($message);

            $this->entityManager->persist($weatherData);
            $this->entityManager->flush();

            $this->logger->info('Données météo enregistrées avec succès pour la station: ' . $macAddress);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du message MQTT: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function loop(): void
    {
        try {
            $this->client->loop(true, true);
        } catch (\Exception $e) {
            $this->logger->error('Loop error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function disconnect(): void
    {
        if ($this->client) {
            $this->client->disconnect();
            $this->logger->info('Disconnected from MQTT broker');
        }
    }
}