<?php

namespace App\Controller\user;

use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

#[Route('/profile')]
#[IsGranted('ROLE_USER')] // Restrict access to logged-in users
class ProfileController extends AbstractController
{
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Check if user is a tutor
        $isTutor = $this->isGranted('ROLE_TUTOR');

        // Create form with tutor-specific fields if applicable
        $form = $this->createForm(ProfileType::class, $user, [
            'userType' => $isTutor ? 'tutor' : null
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadDir = $this->getParameter('pfp_upload_directory');

            // Upload profile picture
            $pfpFile = $form->get('pfp')->getData();
            if ($pfpFile) {
                $newPfpFilename = uniqid().'.'.$pfpFile->guessExtension();
                $pfpFile->move($uploadDir, $newPfpFilename);
                $user->setPfp($newPfpFilename);
            }

            // Upload diploma if applicable
            if ($isTutor) {
                $diplomeFile = $form->get('diplome')->getData();
                if ($diplomeFile) {
                    $newDiplomeFilename = uniqid().'.'.$diplomeFile->guessExtension();
                    $diplomeFile->move($uploadDir, $newDiplomeFilename);
                    $user->setDiplome($newDiplomeFilename);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('profile');
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}