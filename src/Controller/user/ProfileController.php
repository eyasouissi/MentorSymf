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
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/profile')]
#[IsGranted('ROLE_USER')] // Restrict access to logged-in users
class ProfileController extends AbstractController
{
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        // Redirect admin users to the admin dashboard
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_profile'); // Replace 'admin_dashboard' with your actual admin route name
        }

        // Render the regular user profile page
        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
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
                $newPfpFilename = $this->handleFileUpload($pfpFile, $uploadDir);
                $user->setPfp($newPfpFilename);
            }

            // Upload background image
            $bgFile = $form->get('bg')->getData();
            if ($bgFile) {
                $newBgFilename = $this->handleFileUpload($bgFile, $this->getParameter('bg_upload_directory'));
                $user->setBg($newBgFilename);
            }

            // Upload diploma if applicable
            if ($isTutor) {
                $diplomeFile = $form->get('diplome')->getData();
                if ($diplomeFile) {
                    $newDiplomeFilename = $this->handleFileUpload($diplomeFile, $uploadDir);
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

    #[Route('/calculate-profile-progress', name: 'calculate_profile_progress', methods: ['POST'])]
    public function calculateProfileProgress(Request $request): JsonResponse
    {
        $user = $this->getUser(); // Get the logged-in user
        if (!$user) {
            return new JsonResponse(['progress' => 0]);
        }
    
        $isTutor = $this->isGranted('ROLE_TUTOR');
        $progress = 0;
    
        // Base progress for all users
        $progress += 20; // Name is always set
    
        // Additional progress for tutors
        if ($isTutor) {
            $progress += $user->getSpeciality() ? 20 : 0;
            $progress += $user->getDiplome() ? 20 : 0;
            $progress += $user->getBio() ? 20 : 0;
            $progress += ($user->getPfp() && !in_array($user->getPfp(), ['male.jpg', 'female.jpg'])) ? 20 : 0;
        }
        // Additional progress for students
        else {
            $progress += $user->getBio() ? 40 : 0;
            $progress += ($user->getPfp() && !in_array($user->getPfp(), ['male.jpg', 'female.jpg'])) ? 40 : 0;
        }
    
        // Ensure progress does not exceed 100%
        $progress = min($progress, 100);
    
        return new JsonResponse(['progress' => $progress]);
    }
    /**
     * Handles file upload and returns the new filename.
     *
     * @param UploadedFile $file
     * @param string $uploadDir
     * @return string
     */
    private function handleFileUpload(UploadedFile $file, string $uploadDir): string
    {
        $newFilename = uniqid().'.'.$file->guessExtension();
        try {
            $file->move($uploadDir, $newFilename);
        } catch (FileException $e) {
            throw new FileException('Failed to upload file: ' . $e->getMessage());
        }
        return $newFilename;
    }
}