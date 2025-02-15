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
}
