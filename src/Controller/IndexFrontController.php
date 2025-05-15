<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProjectRepository;

class IndexFrontController extends AbstractController
{
    #[Route('/groupfront', name: 'groupfront_index')]
    public function index(ProjectRepository $projectRepository): Response
    {
        // Récupérer tous les projets
        $projects = $projectRepository->findAll();

        // Préparer les données pour FullCalendar
        $events = [];
        foreach ($projects as $project) {
            $events[] = [
                'title' => $project->getTitre(),
                'start' => $project->getDateLimite()->format('Y-m-d\TH:i:s'), // Format datetime avec heure
            ];
        }

        // Passer les données à la vue
        return $this->render('front/groupfront/index.html.twig', [
            'events' => $events,
        ]);
    }
}