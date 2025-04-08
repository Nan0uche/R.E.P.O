<?php

namespace App\Controller;

use App\Entity\WeatherStation;
use App\Form\WeatherStationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/weather')]
class WeatherStationController extends AbstractController
{
    #[Route('/', name: 'app_weather_station_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Tous les utilisateurs voient toutes les stations
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
            // L'utilisateur connecté est automatiquement défini comme propriétaire
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

    #[Route('/{id}/edit', name: 'app_weather_station_edit')]
    public function edit(Request $request, WeatherStation $station, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès
        if (!$this->isGranted('ROLE_ADMIN') && $station->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de modifier cette station.');
        }

        $form = $this->createForm(WeatherStationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La station météo a été modifiée avec succès.');
            return $this->redirectToRoute('app_weather_station_index');
        }

        return $this->render('weather_station/edit.html.twig', [
            'station' => $station,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_weather_station_delete', methods: ['POST'])]
    public function delete(Request $request, WeatherStation $station, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès
        if (!$this->isGranted('ROLE_ADMIN') && $station->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette station.');
        }

        if ($this->isCsrfTokenValid('delete'.$station->getId(), $request->request->get('_token'))) {
            $entityManager->remove($station);
            $entityManager->flush();
            $this->addFlash('success', 'La station météo a été supprimée.');
        }

        return $this->redirectToRoute('app_weather_station_index');
    }
} 