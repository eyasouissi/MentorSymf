<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestingController extends AbstractController
{
    #[Route('/testing', name: 'app_testing')]
    public function index(): Response
    {
        return $this->render('front/testing/index.html.twig', [
            'controller_name' => 'TestingController',
        ]);
    }
}
