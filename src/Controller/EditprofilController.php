<?php
namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class EditprofilController extends AbstractController
{
    #[Route('/editprofile', name: 'app_editprofil')]
    public function index(Request $request): Response
    {
        $profile = $this->getUser()->getProfile();  // Assuming the profile is tied to the logged-in user
        $form = $this->createForm(ProfileType::class, $profile);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the profile data
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Redirect to the profile page or show a success message
            return $this->redirectToRoute('app_profil');
        }

        // Render form in template
        return $this->render('front/profil/editProfil.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}


