<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function indexForum(): Response
    {
        return $this->render('front/forum/forum.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }



    // faire appel fel back 

    #[Route('/forumB', name: 'app_forumB')]
    public function indexForumB(): Response
    {
        return $this->render('back/forum/forum.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }
}
