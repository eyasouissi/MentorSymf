<?php

namespace App\Controller;

use App\Entity\GroupStudent;
use App\Entity\User;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\UserRepository; // Assure-toi que UserRepository est importé
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/group')]
final class GroupController extends AbstractController
{
    #[Route('/', name: 'app_group_index', methods: ['GET'])]
    public function index(GroupRepository $groupRepository): Response
    {
        return $this->render('back/group/index.html.twig', [
            'groupStudents' => $groupRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $groupStudent = new GroupStudent();
        $form = $this->createForm(GroupType::class, $groupStudent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $groupStudent);
            $entityManager->persist($groupStudent);
            $entityManager->flush();
            
            $this->addFlash('success', '✅ Group successfully created!');
            return $this->redirectToRoute('app_group_index');
        }

        return $this->render('back/group/new.html.twig', [
            'groupStudent' => $groupStudent,
            'form' => $form->createView(),
        ]);
    }
    

    private function handleImageUpload($form, $groupStudent): void
    {
        $file = $form->get('image')->getData();

        if ($file instanceof UploadedFile) {
            $uploadsDirectory = $this->getParameter('groupimage_directory');
            $newFilename = uniqid() . '.' . $file->guessExtension();

            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension = strtolower($file->guessExtension());

            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->addFlash('danger', '❌ Invalid file type. Only JPG, JPEG, PNG are allowed.');
                return;
            }

            try {
                $file->move($uploadsDirectory, $newFilename);
                $groupStudent->setImage($newFilename);
                $this->addFlash('success', '✅ Image uploaded successfully!');
            } catch (FileException $e) {
                $this->addFlash('danger', '❌ Failed to upload the image: ' . $e->getMessage());
            }
        }
    }

    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(GroupStudent $groupStudent): Response
    {
        return $this->render('back/group/show.html.twig', [
            'groupStudent' => $groupStudent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GroupStudent $groupStudent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroupType::class, $groupStudent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/group/edit.html.twig', [
            'groupStudent' => $groupStudent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_group_delete', methods: ['POST'])]
    public function delete(Request $request, GroupStudent $groupStudent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupStudent->getId(), $request->request->get('_token'))) {
            $entityManager->remove($groupStudent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete_all', name: 'app_group_delete_all', methods: ['POST'])]
    public function deleteAllGroups(EntityManagerInterface $entityManager, GroupRepository $groupRepository): RedirectResponse
    {
        $groups = $groupRepository->findAll();

        foreach ($groups as $group) {
            $entityManager->remove($group);
        }

        $entityManager->flush();

        $this->addFlash('success', 'All groups have been deleted.');
        return $this->redirectToRoute('app_group_index');
    }
}
