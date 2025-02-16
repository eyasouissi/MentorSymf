<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function indexPost(): Response
    {
        return $this->render('front/post/Post.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }


            // faire appel fel back 

            #[Route('/postB', name: 'app_postB')]
     public function indexPostB(): Response
     {
                return $this->render('back/post/Post.html.twig', [
                    'controller_name' => 'PostController',
                ]);
     }

}
