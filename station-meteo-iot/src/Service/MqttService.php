<?php

namespace App\Service;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\WeatherData;
use App\Entity\WeatherStation;

class MqttService
{
    private $mqttClient;
    private $logger;
    private $entityManager;
    private $brokerHost;
    private $brokerPort;
    private $brokerUsername;
    private $brokerPassword;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        string $brokerHost = 'localhost',
        int $brokerPort = 1883,
        string $brokerUsername = '',
        string $brokerPassword = ''
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->brokerHost = $brokerHost;
        $this->brokerPort = $brokerPort;
        $this->brokerUsername = $brokerUsername;
        $this->brokerPassword = $brokerPassword;
    }

    public function connect(): void
    {
        try {
            $connectionSettings = new ConnectionSettings();
            
            if (!empty($this->brokerUsername)) {
                $connectionSettings->setUsername($this->brokerUsername);
            }
            
            if (!empty($this->brokerPassword)) {
                $connectionSettings->setPassword($this->brokerPassword);
            }
            
            $this->mqttClient = new MqttClient($this->brokerHost, $this->brokerPort);
            $this->mqttClient->connect($connectionSettings);
            
            $this->logger->info('Connected to MQTT broker');
        } catch (\Exception $e) {
            $this->logger->error('Failed to connect to MQTT broker: ' . $e->getMessage());
            throw $e;
        }
    }

    public function subscribe(string $topic): void
    {
        try {
            if (!$this->mqttClient) {
                $this->connect();
            }
            
            $this->mqttClient->subscribe($topic, function ($topic, $message) {
                $this->handleMessage($topic, $message);
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
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->logger->warning('Invalid JSON message received: ' . $message);
                return;
            }
            
            // Extraire l'ID de la station du topic (format: weather/station/{id})
            $topicParts = explode('/', $topic);
            $stationId = end($topicParts);
            
            // Récupérer la station météo
            $station = $this->entityManager->getRepository(WeatherStation::class)->find($stationId);
            
            if (!$station) {
                $this->logger->warning('Station not found for ID: ' . $stationId);
                return;
            }
            
            // Créer une nouvelle entrée de données météo
            $weatherData = new WeatherData();
            $weatherData->setStation($station);
            $weatherData->setTemperature($data['temperature'] ?? null);
            $weatherData->setHumidity($data['humidity'] ?? null);
            $weatherData->setPressure($data['pressure'] ?? null);
            $weatherData->setWindSpeed($data['windSpeed'] ?? null);
            $weatherData->setWindDirection($data['windDirection'] ?? null);
            $weatherData->setRainfall($data['rainfall'] ?? null);
            $weatherData->setTimestamp(new \DateTime());
            
            $this->entityManager->persist($weatherData);
            $this->entityManager->flush();
            
            $this->logger->info('Weather data saved for station: ' . $stationId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to handle message: ' . $e->getMessage());
        }
    }

    public function disconnect(): void
    {
        if ($this->mqttClient) {
            $this->mqttClient->disconnect();
            $this->logger->info('Disconnected from MQTT broker');
        }
    }
} 