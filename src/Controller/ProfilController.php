<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profile', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('front/user/profil.html.twig');
    }
}
