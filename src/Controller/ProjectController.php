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
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route(name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('back/project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_project_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement du PDF
            $pdfFile = $form->get('fichier_pdf')->getData();
            if ($pdfFile) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFilename = uniqid() . '.' . $pdfFile->guessExtension();

                try {
                    $pdfFile->move($uploadsDirectory, $newFilename);
                    $project->setFichierPdf($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', '❌ Failed to upload the PDF file.');
                    return $this->redirectToRoute('app_project_add');
                }
            }

            // Gérer le téléchargement de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageDirectory = $this->getParameter('project_images_directory');
                $newImageName = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($imageDirectory, $newImageName);
                    $project->setImage($newImageName);
                } catch (FileException $e) {
                    $this->addFlash('danger', '❌ Failed to upload the image.');
                    return $this->redirectToRoute('app_project_add');
                }
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', '✅ Project successfully added!');
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('back/project/add.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('back/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le téléchargement du PDF
            $pdfFile = $form->get('fichier_pdf')->getData();
            if ($pdfFile) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFilename = uniqid() . '.' . $pdfFile->guessExtension();

                try {
                    $pdfFile->move($uploadsDirectory, $newFilename);
                    $project->setFichierPdf($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', '❌ Failed to upload the PDF file.');
                    return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
                }
            }

            // Gérer le téléchargement de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageDirectory = $this->getParameter('project_images_directory');
                $newImageName = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($imageDirectory, $newImageName);
                    $project->setImage($newImageName);
                } catch (FileException $e) {
                    $this->addFlash('danger', '❌ Failed to upload the image.');
                    return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
                }
            }

            $entityManager->flush();

            $this->addFlash('info', '✏️ Project successfully updated!');
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('back/project/edit.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_project_delete', methods: ['POST'])]
public function delete($id, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): RedirectResponse
{
    $project = $projectRepository->find($id);

    if ($project) {
        // Supprimer le fichier PDF associé
        $pdfFile = $this->getParameter('uploads_directory') . '/' . $project->getFichierPdf();
        if ($project->getFichierPdf() && file_exists($pdfFile)) {
            unlink($pdfFile);
        }

        // Supprimer l'image associée
        $imageFile = $this->getParameter('project_images_directory') . '/' . $project->getImage();
        if ($project->getImage() && file_exists($imageFile)) {
            unlink($imageFile);
        }

        // Supprimer le projet de la base de données
        $entityManager->remove($project);
        $entityManager->flush();

        $this->addFlash('danger', '🗑️ Project deleted successfully.');
    }

    return $this->redirectToRoute('app_project_index');
}

#[Route('/delete-all', name: 'app_project_delete_all', methods: ['POST'])]
public function deleteAll(EntityManagerInterface $entityManager): Response
{
    $projects = $entityManager->getRepository(Project::class)->findAll();

    foreach ($projects as $project) {
        // Supprimer le fichier PDF associé
        $pdfFile = $this->getParameter('uploads_directory') . '/' . $project->getFichierPdf();
        if ($project->getFichierPdf() && file_exists($pdfFile)) {
            unlink($pdfFile);
        }

        // Supprimer l'image associée
        $imageFile = $this->getParameter('project_images_directory') . '/' . $project->getImage();
        if ($project->getImage() && file_exists($imageFile)) {
            unlink($imageFile);
        }

        // Supprimer le projet de la base de données
        $entityManager->remove($project);
    }

    $entityManager->flush();
    $this->addFlash('danger', '🗑️ All projects deleted successfully.');

    return $this->redirectToRoute('app_project_index');
}
#[Route('/projects', name: 'app_project_front', methods: ['GET'])]
public function front(ProjectRepository $projectRepository, Request $request): Response
{
    $projects = $projectRepository->findAll();

    return $this->render('front/project/index.html.twig', [
        'projects' => $projects,
    ]);
}



}