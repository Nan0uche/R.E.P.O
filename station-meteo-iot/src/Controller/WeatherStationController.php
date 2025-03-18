<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\User;
use App\Repository\WeatherDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/weather')]
#[IsGranted('ROLE_USER')]
class WeatherStationController extends AbstractController
{
    #[Route('/', name: 'app_weather_station')]
    public function index(WeatherDataRepository $weatherDataRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $weatherData = $weatherDataRepository->findBy(
            ['user' => $user],
            ['recordedAt' => 'DESC'],
            10
        );

        $latestData = null;
        if (!empty($weatherData)) {
            $latestData = $weatherData[0];
        }

        return $this->render('weather_station/index.html.twig', [
            'weatherData' => $weatherData,
            'latestData' => $latestData,
        ]);
    }

    #[Route('/add', name: 'app_weather_station_add')]
    public function addWeatherData(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = new WeatherData();
        $data->setRecordedAt(new \DateTime());
        
        // Simuler des données météo pour la démonstration
        if ($request->isMethod('POST')) {
            $data->setTemperature($request->request->get('temperature', 0));
            $data->setHumidity($request->request->get('humidity', 0));
            $data->setPressure($request->request->get('pressure', 0));
            $data->setWindSpeed($request->request->get('windSpeed'));
            $data->setWindDirection($request->request->get('windDirection'));
            $data->setUser($this->getUser());
            
            $entityManager->persist($data);
            $entityManager->flush();
            
            $this->addFlash('success', 'Les données météo ont été enregistrées avec succès !');
            return $this->redirectToRoute('app_weather_station');
        }
        
        return $this->render('weather_station/add.html.twig', [
            'weatherData' => $data,
        ]);
    }
}
