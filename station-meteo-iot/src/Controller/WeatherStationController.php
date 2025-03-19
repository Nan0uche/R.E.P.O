<?php

namespace App\Controller;

use App\Entity\WeatherStation;
use App\Form\WeatherStationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/weather')]
class WeatherStationController extends AbstractController
{
    #[Route('/', name: 'app_weather_station_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $stations = $entityManager
            ->getRepository(WeatherStation::class)
            ->findAll();

        return $this->render('weather_station/index.html.twig', [
            'stations' => $stations,
        ]);
    }

    #[Route('/new', name: 'app_weather_station_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $station = new WeatherStation();
        $form = $this->createForm(WeatherStationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $station->setUser($this->getUser());
            $entityManager->persist($station);
            $entityManager->flush();

            $this->addFlash('success', 'La station météo a été créée avec succès.');
            return $this->redirectToRoute('app_weather_station_index');
        }

        return $this->render('weather_station/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
