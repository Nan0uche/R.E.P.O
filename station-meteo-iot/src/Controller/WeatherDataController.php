<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherDataController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
} 