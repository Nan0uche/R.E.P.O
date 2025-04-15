<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Repository\WeatherDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherDataController extends AbstractController
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/weather-data/latest/{stationId}', name: 'app_weather_data_latest', methods: ['GET'])]
    public function getLatestData(int $stationId): JsonResponse
    {
        $station = $this->entityManager->getRepository(WeatherStation::class)->find($stationId);
        
        if (!$station) {
            return $this->json(['error' => 'Station not found'], Response::HTTP_NOT_FOUND);
        }
        
        $latestData = $this->entityManager->getRepository(WeatherData::class)
            ->findOneBy(['station' => $station], ['timestamp' => 'DESC']);
        
        if (!$latestData) {
            return $this->json(['error' => 'No data available'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'station' => [
                'id' => $station->getId(),
                'name' => $station->getName(),
                'location' => $station->getLocation(),
            ],
            'data' => [
                'temperature' => $latestData->getTemperature(),
                'humidity' => $latestData->getHumidity(),
                'pressure' => $latestData->getPressure(),
                'windSpeed' => $latestData->getWindSpeed(),
                'windDirection' => $latestData->getWindDirection(),
                'rainfall' => $latestData->getRainfall(),
                'timestamp' => $latestData->getTimestamp()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    #[Route('/weather-data/history/{stationId}', name: 'app_weather_data_history', methods: ['GET'])]
    public function getHistory(int $stationId, Request $request): JsonResponse
    {
        $station = $this->entityManager->getRepository(WeatherStation::class)->find($stationId);
        
        if (!$station) {
            return $this->json(['error' => 'Station not found'], Response::HTTP_NOT_FOUND);
        }
        
        $limit = $request->query->getInt('limit', 24); // Par défaut, les 24 dernières heures
        $offset = $request->query->getInt('offset', 0);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('wd')
           ->from(WeatherData::class, 'wd')
           ->where('wd.station = :station')
           ->setParameter('station', $station)
           ->orderBy('wd.timestamp', 'DESC')
           ->setMaxResults($limit)
           ->setFirstResult($offset);
        
        $data = $qb->getQuery()->getResult();
        
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'temperature' => $item->getTemperature(),
                'humidity' => $item->getHumidity(),
                'pressure' => $item->getPressure(),
                'windSpeed' => $item->getWindSpeed(),
                'windDirection' => $item->getWindDirection(),
                'rainfall' => $item->getRainfall(),
                'timestamp' => $item->getTimestamp()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json($result);
    }

    #[Route('/weather-data/latest-by-mac/{macAddress}', name: 'app_weather_data_latest_by_mac', methods: ['GET'])]
    public function getLatestDataByMac(string $macAddress): JsonResponse
    {
        $this->logger->info('Recherche de la station avec l\'adresse MAC: ' . $macAddress);

        $station = $this->entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$station) {
            $this->logger->error('Station non trouvée pour l\'adresse MAC: ' . $macAddress);
            return new JsonResponse(['error' => 'Station not found'], 404);
        }

        $this->logger->info('Station trouvée: ' . $station->getName());

        $latestData = $this->entityManager->getRepository(WeatherData::class)
            ->findOneBy(
                ['station' => $station],
                ['timestamp' => 'DESC']
            );

        if (!$latestData) {
            $this->logger->error('Aucune donnée disponible pour la station: ' . $station->getName());
            return new JsonResponse(['error' => 'No data available'], 404);
        }

        $this->logger->info('Dernière donnée récupérée: ' . $latestData->getTimestamp()->format('Y-m-d H:i:s'));

        $value = $latestData->getValue();
        $data = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Erreur de parsing JSON pour la valeur: ' . $value);
            return new JsonResponse(['error' => 'Invalid data format'], 500);
        }

        return new JsonResponse([
            'timestamp' => $latestData->getTimestamp()->format('Y-m-d H:i:s'),
            'temperature' => floatval($data['temperature'] ?? null),
            'humidity' => floatval($data['humidity'] ?? null)
        ]);
    }

    #[Route('/weather-data/history-by-mac/{macAddress}', name: 'app_weather_data_history_by_mac', methods: ['GET'])]
    public function getHistoryByMac(Request $request, string $macAddress): JsonResponse
    {
        $station = $this->entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);
        
        if (!$station) {
            return new JsonResponse(['error' => 'Station not found'], 404);
        }
        
        $limit = $request->query->getInt('limit', 24);
        
        $weatherData = $this->entityManager->getRepository(WeatherData::class)
            ->findBy(
                ['station' => $station],
                ['timestamp' => 'DESC'],
                $limit
            );
        
        $result = [];
        foreach ($weatherData as $data) {
            $result[] = [
                'timestamp' => $data->getTimestamp()->format('Y-m-d H:i:s'),
                'type' => $data->getType(),
                'value' => $data->getValue()
            ];
        }
        
        return new JsonResponse([
            'error' => false,
            'data' => $result
        ]);
    }

    #[Route('/weather-data/debug/{macAddress}', name: 'app_weather_data_debug', methods: ['GET'])]
    public function debugData(string $macAddress): Response
    {
        $station = $this->entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);
        
        if (!$station) {
            return new Response("Station non trouvée avec l'adresse MAC: " . $macAddress);
        }
        
        $weatherData = $this->entityManager->getRepository(WeatherData::class)
            ->findBy(['station' => $station], ['timestamp' => 'DESC'], 10);
        
        $content = "<h1>Données de débogage pour la station {$station->getName()}</h1>";
        
        if (empty($weatherData)) {
            $content .= "<p>Aucune donnée disponible pour cette station.</p>";
        } else {
            $content .= "<table border='1'><tr><th>Date</th><th>Type</th><th>Valeur</th></tr>";
            foreach ($weatherData as $data) {
                $content .= "<tr>";
                $content .= "<td>" . $data->getTimestamp()->format('Y-m-d H:i:s') . "</td>";
                $content .= "<td>" . $data->getType() . "</td>";
                $content .= "<td><pre>" . $data->getValue() . "</pre></td>";
                $content .= "</tr>";
            }
            $content .= "</table>";
        }
        
        return new Response($content);
    }

    #[Route('/weather-data/test-create/{macAddress}', name: 'app_weather_data_test_create', methods: ['GET'])]
    public function testCreate(string $macAddress): Response
    {
        $station = $this->entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$station) {
            return new Response("Station non trouvée avec l'adresse MAC: " . $macAddress);
        }

        $weatherData = new WeatherData();
        $weatherData->setStation($station);
        $weatherData->setType('test');
        $weatherData->setValue('{"temperature": 23.5, "humidity": 60.0}');
        $weatherData->setTimestamp(new \DateTime());

        $this->entityManager->persist($weatherData);
        $this->entityManager->flush();

        return new Response("Données de test créées avec succès pour la station " . $station->getName());
    }

    #[Route('/api/weather-data', name: 'api_weather_data', methods: ['GET'])]
    public function getWeatherData(WeatherDataRepository $weatherDataRepository): JsonResponse
    {
        $weatherData = $weatherDataRepository->findAll();

        $data = array_map(function ($entry) {
            return [
                'id' => $entry->getId(),
                'temperature' => $entry->getTemperature(),
                'humidity' => $entry->getHumidity(),
                'pressure' => $entry->getPressure(),
                'timestamp' => $entry->getTimestamp()->format('Y-m-d H:i:s'),
                'station' => $entry->getStation()->getName(),
            ];
        }, $weatherData);

        return new JsonResponse($data);
    }
}