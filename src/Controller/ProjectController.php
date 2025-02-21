<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse; // Make sure you're using the correct RedirectResponse class


#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route(name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('front/project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_project_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Debug the form submission
        if ($form->isSubmitted()) {
            dump('Form submitted');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            dump('Form is valid');
            dump($project); // Inspect project data

            // Handle PDF file upload
            $file = $form->get('fichier_pdf')->getData();
    if ($file) {
        $uploadsDirectory = $this->getParameter('uploads_directory');
        $newFilename = uniqid() . '.' . $file->guessExtension(); // Generate unique filename

        try {
            // Move the uploaded file to the designated uploads directory
            $file->move($uploadsDirectory, $newFilename);
            $project->setFichierPdf($newFilename); // Save filename in the entity
        } catch (FileException $e) {
            $this->addFlash('danger', '❌ Failed to upload the PDF file.');
            return $this->redirectToRoute('app_project_add');
        }
    }
            // Persist project to database
            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', '✅ Project successfully added!');
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('front/project/add.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
        
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('front/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle PDF file upload
            $file = $form->get('fichier_pdf')->getData();
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFilename = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move($uploadsDirectory, $newFilename);
                    $project->setFichierPdf($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', '❌ Failed to upload the PDF file.');
                    return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
                }
            }

            // Update project in the database
            $entityManager->flush();

            $this->addFlash('info', '✏️ Project successfully updated!');
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('front/project/edit.html.twig', [
            'form' => $form->createView(),
            'project' => $project, 
        ]);
    }

    #[Route('/project/{id}/delete', name: 'app_project_delete', methods: ['POST'])]
    public function delete($id, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): RedirectResponse
    {
        // Find the project by ID
        $project = $projectRepository->find($id);
    
        if ($project) {
            // Remove the associated PDF file if it exists
            $pdfFile = $this->getParameter('uploads_directory') . '/' . $project->getFichierPdf();
            if ($project->getFichierPdf() && file_exists($pdfFile)) {
                unlink($pdfFile);
            }
    
            // Delete the project from the database
            $entityManager->remove($project);
            $entityManager->flush();
        }
    
        // Redirect back to the project index page after deletion
        return $this->redirectToRoute('app_project_index');
    }
    

    #[Route('/delete-all', name: 'app_project_delete_all', methods: ['POST'])]
    public function deleteAll(EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();
    
        // Delete all projects and their associated PDF files
        foreach ($projects as $project) {
            $pdfFile = $this->getParameter('uploads_directory') . '/' . $project->getFichierPdf();
            if ($project->getFichierPdf() && file_exists($pdfFile)) {
                unlink($pdfFile);
            }
            $entityManager->remove($project);
        }
    
        $entityManager->flush();
        $this->addFlash('danger', '🗑️ All projects deleted successfully.');
    
        return $this->redirectToRoute('app_project_index');
    }
    
}
