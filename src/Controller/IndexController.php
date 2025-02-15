<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('front/index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }



    #[Route('/contact', name: 'app_contact')]
public function contact(): Response
{
    return $this->render('front/index/contact.html.twig', [
        'controller_name' => 'IndexController',
    ]);
}



#[Route('/about', name: 'app_about')]
public function about(): Response
{
    return $this->render('front/index/about.html.twig', [
        'controller_name' => 'IndexController',
    ]);
}

}
