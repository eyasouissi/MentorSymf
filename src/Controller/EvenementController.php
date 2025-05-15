<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\User;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/view', name: 'evenement_index', methods: ['GET'])]
    public function index(EvenementRepository $evenementRepository): Response
    {
        return $this->render('front/evenement/index.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

   // src/Controller/EvenementController.php

   #[Route('/viewback', name: 'evenement_indexback', methods: ['GET'])]
public function indexback(Request $request, EvenementRepository $evenementRepository): Response
{
    $searchTerm = $request->query->get('search', ''); // Get the search term from query parameters
    $orderBy = $request->query->get('orderBy', 'ASC'); // Get the order parameter from query (default ASC)

    // Call searchEvenements with search term and order
    $evenements = $searchTerm
        ? $evenementRepository->searchEvenements($searchTerm, $orderBy)
        : $evenementRepository->searchEvenements('', $orderBy);

    return $this->render('back/evenement/index.html.twig', [
        'evenements' => $evenements,
        'searchTerm' => $searchTerm,
        'orderBy' => $orderBy, // Pass orderBy parameter for potential UI handling
    ]);
}

   

    

    #[Route('/new', name: 'evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $evenement = new Evenement();

        // Fetch the user. Adjust logic as necessary.
        $userId = 1; // Example user ID; replace with your actual logic
        $user = $doctrine->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $evenement->setUser($user);

        // Create the form and handle the request
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('imageE')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    // Move the file to the designated directory
                    $file->move($this->getParameter('uploadsDirectory'), $filename);
                    $evenement->setImageE($filename); // Set the filename for the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image. Veuillez réessayer.');
                }
            }

            // Persist the evenement entity
            $entityManager->persist($evenement);
            $entityManager->flush();

            // Add a success message and redirect
            $this->addFlash('success', 'Événement ajouté avec succès !');
            return $this->redirectToRoute('evenement_index');
        }

        // Render the form view
        return $this->render('front/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/newback', name: 'evenement_newback', methods: ['GET', 'POST'])]
    public function newback(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $evenement = new Evenement();

        // Fetch the user. Adjust logic as necessary.
        $userId = 1; // Example user ID; replace with your actual logic
        $user = $doctrine->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $evenement->setUser($user);

        // Create the form and handle the request
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('imageE')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();

                try {
                    // Move the file to the designated directory
                    $file->move($this->getParameter('uploadsDirectory'), $filename);
                    $evenement->setImageE($filename); // Set the filename for the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image. Veuillez réessayer.');
                }
            }

            // Persist the evenement entity
            $entityManager->persist($evenement);
            $entityManager->flush();

            // Add a success message and redirect
            $this->addFlash('success', 'Événement ajouté avec succès !');
            return $this->redirectToRoute('evenement_indexback');
        }

        // Render the form view
        return $this->render('back/evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }


    
    #[Route('/{id}', name: 'evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        // Check if the associated Annonce exists
        if (!$evenement->getAnnonce()) {
            $this->addFlash('error', 'L\'annonce associée n\'existe pas.');
            return $this->redirectToRoute('evenement_index'); // Redirect to the event index or another appropriate route
        }
    
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('imageE')->getData();
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                try {
                    $file->move($this->getParameter('uploadsDirectory'), $filename);
                    $evenement->setImageE($filename); // Update the filename in the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image. Veuillez réessayer.');
                }
            }
    
            $entityManager->flush();
            $this->addFlash('success', 'Événement mis à jour avec succès !');
    
            return $this->redirectToRoute('evenement_index');
        }
    
        return $this->render('front/evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }
    
   #[Route('/{id}/editback', name: 'evenement_editback', methods: ['GET', 'POST'])]
public function editback(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(EvenementType::class, $evenement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $file */
        $file = $form->get('imageE')->getData();
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->getParameter('uploadsDirectory'), $filename);
                $evenement->setImageE($filename); // Update the filename in the entity
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image. Veuillez réessayer.');
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Événement mis à jour avec succès !');

        return $this->redirectToRoute('evenement_indexback');  // Make sure you redirect to the correct route
    }

    return $this->render('back/evenement/edit.html.twig', [
        'evenement' => $evenement,
        'form' => $form->createView(),
    ]);
}

    
    #[Route('/{id}', name: 'evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'Événement supprimé avec succès !');
        }

        return $this->redirectToRoute('evenement_index');
    }

    #[Route('/back/{id}', name: 'evenement_deleteback', methods: ['POST'])]
    public function deleteback(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'Événement supprimé avec succès !');
        }
    
        return $this->redirectToRoute('evenement_indexback'); // Ensure this route exists
    }
    
}
