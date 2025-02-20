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
}
