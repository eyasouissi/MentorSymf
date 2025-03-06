<?php

namespace App\Controller;

use App\Form\AdminProfileType; // Use AdminProfileType form
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')] // Restrict access to admins
class AdminProfileController extends AbstractController
{
    #[Route('/profile', name: 'admin_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('back/admin/adminProfile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'admin_edit_profile', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Create form with AdminProfileType
        $form = $this->createForm(AdminProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadDir = $this->getParameter('pfp_upload_directory');

            // Handle profile picture upload
            $pfpFile = $form->get('pfp')->getData();
            if ($pfpFile) {
                $newPfpFilename = uniqid().'.'.$pfpFile->guessExtension();
                try {
                    $pfpFile->move($uploadDir, $newPfpFilename);
                    $user->setPfp($newPfpFilename);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'Profile picture upload failed!');
                    return $this->redirectToRoute('admin_edit_profile');
                }
            }

            // Handle background image upload
            $bgFile = $form->get('bg')->getData();
            if ($bgFile) {
                $filename = uniqid().'.'.$bgFile->guessExtension();
                try {
                    $bgFile->move($this->getParameter('bg_upload_directory'), $filename);
                    $user->setBg($filename);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'Background image upload failed!');
                    return $this->redirectToRoute('admin_edit_profile');
                }
            }

            // Save changes to the database
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('back/admin/edit_admin_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
