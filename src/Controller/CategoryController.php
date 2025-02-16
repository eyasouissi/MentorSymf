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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfToken;  // <-- Ajouter cette ligne
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface; // Si tu utilises le gestionnaire de tokens CSRF

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
        // Créer une nouvelle instance de Category
        $category = new Category();
    
        // Créer le formulaire
        $form = $this->createForm(CategoryType::class, $category);
    
        // Gérer la requête HTTP
        $form->handleRequest($request);
    
        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création si elle n'est pas déjà remplie
            if (!$category->getCreatedAt()) {
                $category->setCreatedAt(new \DateTime());  // Assigner la date actuelle
            }
    
            // Persister et enregistrer la catégorie en base de données
            $entityManager->persist($category);
            $entityManager->flush();
    
    
            // Redirection vers la liste des catégories
            return $this->redirectToRoute('category_list');
        }
    
        // Si le formulaire n'est pas soumis ou n'est pas valide, afficher le formulaire
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
            // Gérer l'icône si présente
            $iconFile = $form->get('icon')->getData();
            if ($iconFile) {
                $newFilename = uniqid() . '.' . $iconFile->guessExtension();
                $iconFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $category->setIcon($newFilename);
            }

            // Sauvegarder l'entité modifiée
            $entityManager->flush();

            return $this->redirectToRoute('category_list');
        }

        return $this->render('back/category/editCategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    #[Route('/category/{id}/delete', name: 'category_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, Request $request): RedirectResponse
    {
        // Récupérer le token CSRF depuis la requête
        $csrfToken = $request->request->get('_token');

        // Vérifier la validité du token CSRF
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('category_delete', $csrfToken))) {
            throw new InvalidCsrfTokenException('Token CSRF invalide');
        }

        // Supprimer la catégorie
        $entityManager->remove($category);
        $entityManager->flush();

        // Message flash pour informer l'utilisateur
        $this->addFlash('success', 'Catégorie supprimée avec succès !');

        // Redirection vers la liste des catégories
        return $this->redirectToRoute('category_list');
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
    return $this->render('front/category/details.html.twig', [
        'category' => $category,
        //'courses' => $category->getCourses(), // Une fois l'entité Course créée
    ]);
}



}
