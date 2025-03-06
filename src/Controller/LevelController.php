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
use Symfony\Component\Security\Http\Attribute\IsGranted;
//use App\Service\FileConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ZipArchive;
use Psr\Log\LoggerInterface;



class LevelController extends AbstractController
{
    #[Route('/course/{id}/add-level', name: 'add_level')]
    #[IsGranted('ROLE_TUTOR')] 
    public function addLevel(Request $request, Courses $course, EntityManagerInterface $em): Response
    {
        $level = new Level();
        $level->setCourse($course);

        $form = $this->createForm(LevelType::class, $level);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les fichiers uploadés
            foreach ($form->get('files')->getData() as $file) {
                if (!$file) {
                    continue;
                }

                // Vérifier le format du fichier
                $allowedExtensions = ['pdf', 'docx', 'txt'];
                $extension = $file->guessExtension();
                if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('danger', 'Format de fichier non autorisé.');
                    return $this->redirectToRoute('course_details', ['id' => $course->getId()]);
                }

                // Enregistrement du fichier
                $fileName = md5(uniqid()) . '.' . $extension;
                $file->move($this->getParameter('diploma_directory'), $fileName);

                $fileEntity = new File();
                $fileEntity->setFileName($fileName);
                $fileEntity->setLevel($level);
                $em->persist($fileEntity);

                $level->addFile($fileEntity);
            }

            $em->persist($level);
            $em->flush();

            $this->addFlash('success', 'Niveau ajouté avec succès.');
            return $this->redirectToRoute('course_details', ['id' => $course->getId()]);
        }

        return $this->render('front/level/addLevel.html.twig', [
            'form' => $form->createView(),
            'course' => $course
        ]);
    }



    #[Route('/level/upload/{id}', name: 'upload_file', methods: ['POST'])]
    #[IsGranted('ROLE_TUTOR')] 
    public function uploadFile(Request $request, Level $level, EntityManagerInterface $entityManager): Response
    {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile) {
            // Vérifier la taille du fichier (max 5 Mo)
            if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('danger', 'Le fichier est trop volumineux. Taille maximale : 5 Mo.');
                return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
            }

            // Vérifier le format du fichier
            $allowedExtensions = ['pdf', 'docx', 'txt'];
            $extension = $uploadedFile->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                $this->addFlash('danger', 'Format de fichier non autorisé.');
                return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
            }

            // Enregistrer le fichier
            $fileName = md5(uniqid()) . '.' . $extension;
            $uploadedFile->move($this->getParameter('diploma_directory'), $fileName);

            // Enregistrement en base de données
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
    #[IsGranted('ROLE_TUTOR')] 
    public function deleteLevel(Level $level, EntityManagerInterface $em, Filesystem $filesystem): Response
    {
        // Supprimer les fichiers liés
        foreach ($level->getFiles() as $file) {
            $filePath = $this->getParameter('diploma_directory') . '/' . $file->getFileName();
            if ($filesystem->exists($filePath)) {
                $filesystem->remove($filePath);
            }
            $em->remove($file);
        }

        // Supprimer le niveau
        $em->remove($level);
        $em->flush();

        $this->addFlash('success', 'Le niveau a été supprimé avec succès.');
        return $this->redirectToRoute('course_details', ['id' => $level->getCourse()->getId()]);
    }

    #[Route('/level/{id}/edit', name: 'edit_level')]
    #[IsGranted('ROLE_TUTOR')] 
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



    #[Route('/level/{id}/view', name: 'level_view')]
    public function viewLevel(Level $level): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        
        if (!$user) {
            // Redirect to login if the user is not authenticated
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir ce niveau.');
        }
    
        // Check if there are files associated with the level
        if ($level->getFiles()->isEmpty()) {
            // Throw a 404 exception if no files are found for the level
            throw $this->createNotFoundException('Aucun document trouvé pour ce niveau.');
        }
    
        // Render the view template with the level data
        return $this->render('front/level/view_level.html.twig', [
            'level' => $level,
        ]);
    }
    

   /* #[Route('/level/{id}/complete', name: 'complete_level', methods: ['POST'])]
    public function completeLevel(Level $level, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Vérification si l'utilisateur a bien complété le niveau précédent
        $previousLevel = $level->getPreviousLevel();
        if ($previousLevel && !$user->hasCompletedLevel($previousLevel)) {
            return $this->json(['error' => 'Vous devez terminer le niveau précédent avant de compléter celui-ci.'], Response::HTTP_BAD_REQUEST);
        }
    
        // Ajouter le niveau à la liste des niveaux complétés par l'utilisateur
        $user->completeLevel($level);
        $em->persist($user);
        $em->flush();
    
        // Répondre avec succès en JSON
        return $this->json(['success' => 'Niveau complété avec succès.']);
    }*/


    #[Route('/level/{id}/complete', name: 'complete_level', methods: ['POST'])]
public function completeLevel(Level $level, EntityManagerInterface $em): Response
{
    $user = $this->getUser();

    if (!$user) {
        return $this->json(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
    }

    // Vérification si l'utilisateur a bien complété le niveau précédent
    $previousLevel = $level->getPreviousLevel();
    if ($previousLevel && !$user->hasCompletedLevel($previousLevel)) {
        return $this->json(['error' => 'Vous devez terminer le niveau précédent avant de compléter celui-ci.'], Response::HTTP_BAD_REQUEST);
    }

    // Ajouter le niveau à la liste des niveaux complétés par l'utilisateur
    $user->completeLevel($level);
    $em->persist($user);
    $em->flush();

    // Répondre avec succès en JSON
    return $this->json(['success' => 'Level successfully completed.']);
}

    
#[Route('/level/{id}/download', name: 'download_all_files')]
public function downloadAllFiles(Level $level, LoggerInterface $logger): Response
{
    // Vérifiez que l'utilisateur est autorisé à télécharger les fichiers
    $this->denyAccessUnlessGranted('ROLE_USER'); // Ou une autre condition

    // Vérifiez qu'il y a des fichiers à télécharger
    if ($level->getFiles()->isEmpty()) {
        throw new \Exception("Aucun fichier disponible pour ce niveau.");
    }

    // Créez une archive ZIP temporaire
    $zip = new ZipArchive();
    $zipFileName = sys_get_temp_dir() . '/' . uniqid('level_files_', true) . '.zip';

    if ($zip->open($zipFileName, ZipArchive::CREATE) !== true) {
        $logger->error("Impossible de créer l'archive ZIP : {filename}", ['filename' => $zipFileName]);
        throw new \Exception("Impossible de créer l'archive ZIP.");
    }

    // Ajoutez chaque fichier à l'archive
    foreach ($level->getFiles() as $file) {
        $filePath = $this->getParameter('diploma_directory') . '/' . $file->getFileName();
        if (file_exists($filePath)) {
            $logger->info("Ajout du fichier : {filePath}", ['filePath' => $filePath]);
            if ($zip->addFile($filePath, $file->getFileName())) {
                $logger->info("Fichier ajouté avec succès : {filePath}", ['filePath' => $filePath]);
            } else {
                $logger->error("Échec de l'ajout du fichier : {filePath}", ['filePath' => $filePath]);
            }
        } else {
            $logger->warning("Fichier non trouvé : {filePath}", ['filePath' => $filePath]);
        }
    }

    $zip->close();

    // Vérifiez que l'archive existe et contient des fichiers
    if (!file_exists($zipFileName)) {
        $logger->error("L'archive ZIP n'a pas été créée : {filename}", ['filename' => $zipFileName]);
        throw new \Exception("L'archive ZIP n'a pas été créée.");
    }

    // Vérifiez le contenu de l'archive
    if ($zip->open($zipFileName) === true) {
        $numFiles = $zip->numFiles;
        $logger->info("Nombre de fichiers dans l'archive : {numFiles}", ['numFiles' => $numFiles]);
        if ($numFiles === 0) {
            $logger->error("Aucun fichier valide n'a été ajouté à l'archive : {filename}", ['filename' => $zipFileName]);
            throw new \Exception("Aucun fichier valide n'a été ajouté à l'archive.");
        }
        for ($i = 0; $i < $numFiles; $i++) {
            $logger->info("Fichier dans l'archive : {fileName}", ['fileName' => $zip->getNameIndex($i)]);
        }
        $zip->close();
    } else {
        $logger->error("Impossible d'ouvrir l'archive pour vérification : {filename}", ['filename' => $zipFileName]);
        throw new \Exception("Impossible de vérifier le contenu de l'archive.");
    }

    // Envoyez l'archive ZIP en réponse
    $response = new BinaryFileResponse($zipFileName);
    $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'level_files.zip'
    );

    // Supprimez l'archive temporaire après l'envoi
    $response->deleteFileAfterSend(true);

    return $response;
}
}