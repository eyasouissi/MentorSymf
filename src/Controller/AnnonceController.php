<?php
namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class AnnonceController extends AbstractController
{ #[Route('/annonce/{id}/qr', name: 'app_annonce_qr', requirements: ['id' => '\d+'])]
    public function generateQrCode(Annonce $annonce): Response
    {
        // Créer le QR code avec l'URL de l'annonce
        $url = $this->generateUrl('app_annonce_show', ['id' => $annonce->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $qrCode = new QrCode($url);
    
        // Générer l'image du QR code en format PNG
        $response = new Response($qrCode->writeString());
        $response->headers->set('Content-Type', 'image/png');
    
        return $response;
    }
    #[Route('/annonce/statistics', name: 'ann_statistics')]
    public function statisticsAction(
        AnnonceRepository $AnnonceRepository,
    ): Response {
        // Fetch the statistics for the forum (daily)
        $forumsByDate = $AnnonceRepository->countByDate();
        
        // Prepare data for the pie chart
        $labels = [];
        $data = [];
        foreach ($forumsByDate as $forum) {
            $labels[] = $forum['date']; // Add date to labels
            $data[] = $forum['count'];  // Add count to data
        }
    
        // Fetch the statistics for the forum (monthly)
        $forumsByMonth = $AnnonceRepository->countByMonth();
    
        // Prepare monthly statistics for the view
        $monthLabels = [];
        $monthData = [];
        foreach ($forumsByMonth as $forum) {
            $monthLabels[] = $forum['month']; // Add month to labels
            $monthData[] = $forum['count'];  // Add count to data
        }
    
        // Render the template with all the statistics
        return $this->render('back/annonce/statistics.html.twig', [
            'labels' => $labels,           // Labels for the chart (daily)
            'data' => $data,               // Data for the chart (daily)
            'monthLabels' => $monthLabels, // Labels for the chart (monthly)
            'monthData' => $monthData,  
               // Data for the chart (monthly)
        ]);
    }
    
    #[Route('/annonces', name: 'app_annonces')]
public function index(Request $request, AnnonceRepository $annonceRepository): Response
{
    $searchTerm = $request->query->get('search', '');
    $orderBy = $request->query->get('orderBy', 'date_a');  // Assurez-vous que 'orderBy' existe
    $page = $request->query->get('page', 1);  // La page par défaut est 1

    // Récupérer les annonces en fonction du tri
    $annonces = $annonceRepository->findBySearchTermAndOrder($searchTerm, $orderBy, $page);

    return $this->render('front/annonce/index.html.twig', [
        'annonces' => $annonces,
    ]);
}

    #[Route('/annoncesback', name: 'app_annoncesback')]
    public function indexback(AnnonceRepository $annonceRepository): Response
    {
        // Fetch all annonces from the database
        $annonces = $annonceRepository->findAll();
    
        return $this->render('back/annonce/index.html.twig', [
            'annonces' => $annonces,
        ]);
    }
    
    #[Route('/annonce/{id}', name: 'app_annonce_show', requirements: ['id' => '\d+'])]
    public function show(Annonce $annonce): Response
    {
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/annonce/ajouter', name: 'ajouter_annonce')]
    public function ajouterAnnonce(Request $req, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, ParameterBagInterface $params): Response
    {
        $annonce = new Annonce();
        
        // Example user ID set to 1 (can be replaced with current user)
        $userId = 1;
        $user = $doctrine->getRepository(User::class)->find($userId);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        // Set the user to the annonce
        $annonce->setUser($user);
        
        // Automatically set the current date for 'date_a'
        $annonce->setDateA(new \DateTime());
    
        // Create and handle the form
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $image = $form->get('image_a')->getData();
            if ($image) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $image->guessExtension();
    
                try {
                    // Move the file to the directory where images are stored
                    $image->move($params->get('uploadsDirectory'), $fileName);
                    $annonce->setImageA($fileName);  // Set the image filename in the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }
    
            // Persist and save the annonce
            $entityManager->persist($annonce);
            $entityManager->flush();
    
            // Redirect to the annonces listing page after successful submission
            return $this->redirectToRoute('app_annonces');
        }
    
        // Render the form
        return $this->render('front/annonce/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/annonce/{id}/edit', name: 'app_annonce_edit', requirements: ['id' => '\d+'])]
    public function edit(Annonce $annonce, Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
    
        // Store the old image name before form submission (in case no new image is uploaded)
        $oldImage = $annonce->getImageA();
    
        // Handle the form submission
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $image = $form->get('image_a')->getData();
            
            if ($image) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $image->guessExtension();
    
                try {
                    // Move the file to the directory where images are stored
                    $image->move($params->get('uploadsDirectory'), $fileName);
                    $annonce->setImageA($fileName);  // Set the image filename in the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            } else {
                // If no new image, keep the old image
                $annonce->setImageA($oldImage);
            }
    
            // Persist and save the annonce
            $entityManager->flush();
    
            // Redirect to the annonces listing page after successful submission
            return $this->redirectToRoute('app_annonces');
        }
    
        return $this->render('front/annonce/ajouter.html.twig', [
            'form' => $form->createView(),
            'annonce' => $annonce,
        ]);
    }
    #[Route('/annonce/{id}/editback', name: 'app_annonce_editback', requirements: ['id' => '\d+'])]
    public function editback(Annonce $annonce, Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        
        // Store the old image name before form submission (in case no new image is uploaded)
        $oldImage = $annonce->getImageA();
        
        // Handle the form submission
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $image = $form->get('image_a')->getData();
            
            if ($image) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $image->guessExtension();
        
                try {
                    // Move the file to the directory where images are stored
                    $image->move($params->get('uploadsDirectory'), $fileName);
                    $annonce->setImageA($fileName);  // Set the image filename in the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            } else {
                // If no new image, keep the old image
                $annonce->setImageA($oldImage);
            }
        
            // Persist and save the annonce
            $entityManager->flush();
        
            // Redirect to the annonces listing page after successful submission
            return $this->redirectToRoute('app_annoncesback');
        }
        
        return $this->render('back/annonce/ajouter.html.twig', [
            'form' => $form->createView(),
            'annonce' => $annonce,
        ]);
    }
    
    #[Route('/annonce/{id}/delete', name: 'app_annonce_delete', requirements: ['id' => '\d+'])]
    public function delete(Annonce $annonce, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($annonce);
        $entityManager->flush();

        return $this->redirectToRoute('app_annonces');
    }

    #[Route('/annonce/{id}/deleteback', name: 'app_annonce_deleteback', requirements: ['id' => '\d+'])]
public function deleteback(Annonce $annonce, EntityManagerInterface $entityManager): Response
{
    $entityManager->remove($annonce);
    $entityManager->flush();

    return $this->redirectToRoute('app_annoncesback');
}



    #[Route('/annonce/ajouterback', name: 'ajouter_annonceback')]

    public function ajouterAnnonceback(Request $req, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, ParameterBagInterface $params): Response
    {
        $annonce = new Annonce();
        
        // Example user ID set to 1 (can be replaced with current user)
        $userId = 1;
        $user = $doctrine->getRepository(User::class)->find($userId);
        
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        
        // Set the user to the annonce
        $annonce->setUser($user);
        
        // Automatically set the current date for 'date_a'
        $annonce->setDateA(new \DateTime());
    
        // Create and handle the form
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $image = $form->get('image_a')->getData();
            if ($image) {
                // Generate a unique file name
                $fileName = uniqid() . '.' . $image->guessExtension();
    
                try {
                    // Move the file to the directory where images are stored
                    $image->move($params->get('uploadsDirectory'), $fileName);
                    $annonce->setImageA($fileName);  // Set the image filename in the entity
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }
    
            // Persist and save the annonce
            $entityManager->persist($annonce);
            $entityManager->flush();
    
            // Redirect to the annonces listing page after successful submission
            return $this->redirectToRoute('app_annoncesback');
        }
    
        // Render the form
        return $this->render('back/annonce/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
