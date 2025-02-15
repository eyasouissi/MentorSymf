<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_event')]
    public function indexEvents(): Response
    {
        return $this->render('front/events/Events.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }
}
