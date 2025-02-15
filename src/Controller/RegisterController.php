<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(Request $request): Response
    {
        $profile = new Profile();
        $form = $this->createForm(ProfileType::class, $profile);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the profile data
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->flush();

            // Redirect to the profile page or show a success message
            return $this->redirectToRoute('app_profil');
        }

        // Render form in template
        return $this->render('front/user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
