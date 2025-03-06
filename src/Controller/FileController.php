<?php
// src/Controller/FileController.php
namespace App\Controller;

use App\Entity\File;
use App\Entity\Level;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

class FileController extends AbstractController
{
    #[Route('/level/{levelId}/add-file', name: 'add_file')]
    public function addFile(int $levelId, Request $request, EntityManagerInterface $em)
    {
        $level = $em->getRepository(Level::class)->find($levelId);
        if (!$level) {
            throw $this->createNotFoundException('Level not found.');
        }

        // Handle file upload
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file'); // file is the form field name

        if ($uploadedFile) {
            $filename = uniqid().'.'.$uploadedFile->getClientOriginalExtension();
            $directory = $this->getParameter('upload_directory'); // Configure this in services.yaml

            $uploadedFile->move($directory, $filename);

            // Create File entity
            $file = new File();
            $file->setFileName($filename);
            $file->setIsViewed(false); // initially false, user has not viewed yet
            $file->setLevel($level);

            $em->persist($file);
            $em->flush();
        }

        return $this->redirectToRoute('level_show', ['levelId' => $levelId]);
    }


    // FileController.php
    #[Route('/file/delete/{fileId}', name: 'delete_file', methods: ['POST'])]
    public function deleteFile(int $fileId, EntityManagerInterface $em)
    {
        $file = $em->getRepository(File::class)->find($fileId);
        if (!$file) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }
    
        // Suppression du fichier sur le disque
        $filesystem = new Filesystem();
        $filePath = $this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $file->getFileName();
        
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    
        // Suppression du fichier dans la base de données
        $em->remove($file);
        $em->flush();
    
        // Redirection après suppression
        // Assurez-vous de rediriger vers le bon paramètre "id"
        return $this->redirectToRoute('course_details', ['id' => $file->getLevel()->getCourse()->getId()]);
    }
    
}
