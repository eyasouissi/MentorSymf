<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AbonnementController extends AbstractController
{
    #[Route('/abonnement', name: 'app_abonnement')]
    public function index(): Response
    {
        return $this->render('front/abonnement/index.html.twig', [
            'controller_name' => 'AbonnementController',
        ]);
    }



// faire appel fel back 

    #[Route('/abonnementB', name: 'app_abonnementB')]
    public function indexB(): Response
    {
        return $this->render('back/abonnement/index.html.twig', [
            'controller_name' => 'AbonnementController',
        ]);
    }
}
