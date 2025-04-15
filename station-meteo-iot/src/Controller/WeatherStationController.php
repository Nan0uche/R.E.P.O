<?php

namespace App\Controller;

use App\Entity\WeatherStation;
use App\Entity\WeatherData;
use App\Form\WeatherStationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/weather-station', name: 'app_weather_station_')]
class WeatherStationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $stations = $entityManager->getRepository(WeatherStation::class)->findAll();

        return $this->render('weather_station/index.html.twig', [
            'stations' => $stations,  // Make sure to pass 'stations', not 'weather_stations'
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $weatherStation = new WeatherStation();
        $form = $this->createForm(WeatherStationType::class, $weatherStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($weatherStation);
            $entityManager->flush();

            return $this->redirectToRoute('app_weather_station_index');
        }

        return $this->render('weather_station/new.html.twig', [
            'weather_station' => $weatherStation,
            'form' => $form,
        ]);
    }

    #[Route('/{macAddress}', name: 'show', methods: ['GET'])]
    public function show(string $macAddress, EntityManagerInterface $entityManager): Response
    {
        $weatherStation = $entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$weatherStation) {
            throw $this->createNotFoundException('Station non trouvée avec l\'adresse MAC: '.$macAddress);
        }

        // N'oubliez pas d'ajouter cette ligne pour retourner une réponse
        return $this->render('weather_station/show.html.twig', [
            'weather_station' => $weatherStation,
        ]);
    }

    #[Route('/{macAddress}/details', name: 'details', methods: ['GET'])]
    public function details(string $macAddress, EntityManagerInterface $entityManager): Response
    {
        $weatherStation = $entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$weatherStation) {
            throw $this->createNotFoundException('Station non trouvée avec l\'adresse MAC: '.$macAddress);
        }

        $weatherData = $entityManager->getRepository(WeatherData::class)
            ->findBy(
                ['station' => $weatherStation],
                ['timestamp' => 'DESC'],
                50
            );

        return $this->render('weather_station/details.html.twig', [
            'weather_station' => $weatherStation,
            'weather_data' => $weatherData,
        ]);
    }

    #[Route('/{macAddress}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $macAddress, EntityManagerInterface $entityManager): Response
    {
        $weatherStation = $entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$weatherStation) {
            throw $this->createNotFoundException('Station non trouvée avec l\'adresse MAC: '.$macAddress);
        }

        $form = $this->createForm(WeatherStationType::class, $weatherStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_weather_station_show', ['macAddress' => $weatherStation->getMacAddress()]);
        }

        return $this->render('weather_station/edit.html.twig', [
            'weather_station' => $weatherStation,
            'form' => $form,
        ]);
    }

    #[Route('/{macAddress}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, string $macAddress, EntityManagerInterface $entityManager): Response
    {
        $weatherStation = $entityManager->getRepository(WeatherStation::class)
            ->findOneBy(['macAddress' => $macAddress]);

        if (!$weatherStation) {
            throw $this->createNotFoundException('Station non trouvée avec l\'adresse MAC: '.$macAddress);
        }

        if ($this->isCsrfTokenValid('delete'.$weatherStation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($weatherStation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_weather_station_index');
    }
}