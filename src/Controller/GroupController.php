<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GroupController extends AbstractController
{
    #[Route('/group', name: 'app_group')]
    public function indexGroup(): Response
    {
        return $this->render('front/group/Group.html.twig', [
            'controller_name' => 'GroupController',
        ]);
    }
}
