<?php
namespace App\Controller;

use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'calendar')]
    public function calendar(EvenementRepository $evenementRepository): Response
    {
        // Retrieve all events
        $events = $evenementRepository->findAll();
    
        $rdvs = [];
        foreach ($events as $event) {
            // Ensure event has valid start and end date
            $startDate = $event->getDateDebut();
            $endDate = $event->getDateFin();
    
            if ($startDate && $endDate) {
                $rdvs[] = [
                    'id' => $event->getId(),
                    'start' => $startDate->format('Y-m-d H:i:s'),
                    'end' => $endDate->format('Y-m-d H:i:s'),
                    'title' => $event->getTitreE(), // Example: Use 'nomE' for event title
                ];
            }
        }
    
        // Convert events to JSON for FullCalendar
        $data = json_encode($rdvs);
    
        return $this->render('front/evenement/calendar.html.twig', [
            'data' => $data,
        ]);
    }
}    