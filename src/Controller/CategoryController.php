<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('back/category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/add', name: 'category_add')]
    public function addCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $iconFile = $form->get('icon')->getData();
            
            if ($iconFile) {
                // Crée un nom unique pour l'icône ou la vidéo
                $filename = uniqid().'.'.$iconFile->guessExtension();
    
                // Déplace le fichier vers le répertoire des uploads
                $iconFile->move(
                    $this->getParameter('upload_directory'),  // Paramètre pour le répertoire de destination
                    $filename
                );
    
                // Enregistre le nom du fichier dans l'entité
                $category->setIcon($filename);
            }
    
            if (!$category->getCreatedAt()) {
                $category->setCreatedAt(new \DateTime());
            }
    
            $entityManager->persist($category);
            $entityManager->flush();
    
            return $this->redirectToRoute('category_list');
        }
    
        return $this->render('back/category/addCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/category/list', name: 'category_list')]
    public function listCategories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('back/category/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}/edit', name: 'category_edit')]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $iconFile = $form->get('icon')->getData();
            if ($iconFile) {
                $newFilename = uniqid() . '.' . $iconFile->guessExtension();
                $iconFile->move($this->getParameter('images_directory'), $newFilename);
                $category->setIcon($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('category_list');
        }

        return $this->render('back/category/editCategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }
    #[Route('/category/{id}/delete', name: 'category_delete', methods: ['POST'])]
    public function delete($id, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): RedirectResponse
    {
        // Trouver la catégorie par ID
        $category = $categoryRepository->find($id);
    
        if ($category) {
            // Supprimer la catégorie
            $entityManager->remove($category);
            $entityManager->flush();
        }
    
        // Rediriger vers la liste des catégories après la suppression
        return $this->redirectToRoute('category_list');  // Redirection correcte
    }
    
    





/////////////////////////////////controlleur du front////////////////////////////////////////


#[Route('/categories', name: 'category_lists')]
public function listCategorie(CategoryRepository $categoryRepository): Response
{
    $categories = $categoryRepository->findAll();

    return $this->render('front/category/list.html.twig', [
        'categories' => $categories,
    ]);
}


#[Route('/category/{id}', name: 'category_details')]
public function details(Category $category): Response
{
    // Récupérer les cours associés à la catégorie
    $courses = $category->getCourses(); // Assurez-vous que getCourses() est une méthode sur l'entité Category

    return $this->render('front/category/details.html.twig', [
        'category' => $category,
        'courses' => $courses, // Passer les cours à la vue
    ]);
}




}
