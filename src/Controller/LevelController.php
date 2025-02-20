<?php

// src/Controller/LevelController.php

namespace App\Controller;

use App\Entity\Level;
use App\Entity\File;
use App\Entity\Courses;
use App\Form\LevelType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;


class LevelController extends AbstractController
{
    #[Route('/course/{id}/add-level', name: 'add_level')]
    public function addLevel(Request $request, Courses $course, EntityManagerInterface $em): Response
    {
        $level = new Level();
        $level->setCourse($course);
    
        $form = $this->createForm(LevelType::class, $level);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les fichiers uploadés
            foreach ($form->get('files')->getData() as $file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
    
                // Créer une nouvelle instance de File et la persister
                $fileEntity = new File();
                $fileEntity->setFileName($fileName);
                $fileEntity->setLevel($level);
                $em->persist($fileEntity); // Persister chaque fichier
    
                $level->addFile($fileEntity); // Ajouter le fichier à Level
            }
    
            $em->persist($level); // Persister Level
            $em->flush();
    
            return $this->redirectToRoute('course_details', ['id' => $course->getId()]);
        }
    
        return $this->render('front/level/addLevel.html.twig', [
            'form' => $form->createView(),
            'course' => $course
        ]);
    }



    #[Route('/level/upload/{id}', name: 'upload_file', methods: ['POST'])]
public function uploadFile(Request $request, Level $level, EntityManagerInterface $entityManager): Response
{
    $uploadedFile = $request->files->get('file');

    if ($uploadedFile) {
        // Vérifier la taille du fichier (max 5 Mo)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('danger', 'Le fichier est trop volumineux. Taille maximale : 5 Mo.');
            return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
        }

        // Générer un nom unique et enregistrer le fichier
        $fileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($this->getParameter('upload_directory'), $fileName);

        // Enregistrer en base de données
        $file = new File();
        $file->setFileName($fileName);
        $file->setLevel($level);

        $entityManager->persist($file);
        $entityManager->flush();

        $this->addFlash('success', 'Fichier uploadé avec succès !');
    }

    return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
}



#[Route('/level/{id}/delete', name: 'delete_level', methods: ['POST', 'DELETE'])]
public function deleteLevel(Level $level, EntityManagerInterface $em): Response
{
    $em->remove($level);
    $em->flush();

    $this->addFlash('success', 'Le niveau a été supprimé avec succès.');
    return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
}



#[Route('/level/{id}/edit', name: 'edit_level')]
public function editLevel(Request $request, Level $level, EntityManagerInterface $em): Response
{
    $form = $this->createForm(LevelType::class, $level);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Le niveau a été mis à jour avec succès.');
        return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
    }

    return $this->render('front/level/editLevel.html.twig', [
        'form' => $form->createView(),
        'level' => $level
    ]);
}

}
