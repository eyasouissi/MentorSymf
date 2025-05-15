<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Offre;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OffreRepository;
use App\Repository\PaiementRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
final class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('front/payment/Payment.html.twig');
    }

    #[Route('/add_offre', name: 'app_add_offre')]
    public function addOffre(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_offre')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir').'/public/uploads', $newFilename);
                $offre->setImageOffre($newFilename);
            }

            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('offre_list');
        }

        return $this->render('back/payment/Payment.html.twig', [
            'f' => $form->createView(),
        ]);
    }

    #[Route('/offre_list', name: 'offre_list')]

public function listOffres(Request $request, OffreRepository $offreRepository): Response
{
    $searchTerm = $request->query->get('search', '');
    $sortOrder = $request->query->get('sort', '');

    // Vérifier si le paramètre search est bien reçu (temporaire pour debug)
    // dump($searchTerm); die;

    $queryBuilder = $offreRepository->createQueryBuilder('o');

    if (!empty($searchTerm)) {
        $queryBuilder->andWhere('o.nom_offre LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%');
    }

    if ($sortOrder === 'asc') {
        $queryBuilder->orderBy('o.prix', 'ASC');
    } elseif ($sortOrder === 'desc') {
        $queryBuilder->orderBy('o.prix', 'DESC');
    }

    $offres = $queryBuilder->getQuery()->getResult();

    if ($request->isXmlHttpRequest()) {
        return $this->json([
            'offres' => array_map(function ($offre) {
                return [
                    'id_offre' => $offre->getIdOffre(),
                    'nom_offre' => $offre->getNomOffre(),
                    'image_offre' => $offre->getImageOffre() ? '/uploads/' . $offre->getImageOffre() : null,
                    'prix' => $offre->getPrix(),
                    'date_debut' => $offre->getDateDebut()->format('Y-m-d'),
                    'date_fin' => $offre->getDateFin() ? $offre->getDateFin()->format('Y-m-d') : null,
                    'description' => $offre->getDescription(),
                ];
            }, $offres),
        ]);
    }

    return $this->render('back/payment/list.html.twig', [
        'offres' => $offres,
        'searchTerm' => $searchTerm,
        'sortOrder' => $sortOrder,
    ]);
}

    
    #[Route('/front/offres', name: 'offre_list_front')]
    public function listOffresFront(OffreRepository $offreRepository): Response
    {
        $offres = $offreRepository->findAll();
        $stripePublicKey = $this->getParameter('stripe.public_key');

        return $this->render('front/payment/Payment.html.twig', [
            'offres' => $offres,
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

    #[Route('/delete_offre/{id}', name: 'app_delete_offre')]
    public function deleteOffre(int $id, EntityManagerInterface $entityManager, OffreRepository $offreRepository): Response
    {
        $offre = $offreRepository->find($id);
    
        if (!$offre) {
            $this->addFlash('error', 'Offer not found.');
            return $this->redirectToRoute('offre_list');
        }
    
        foreach ($offre->getPaiements() as $paiement) {
            $entityManager->remove($paiement);
        }
    
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $offre->getImageOffre();
        if ($offre->getImageOffre() && file_exists($imagePath)) {
            unlink($imagePath);
        }
    
        $entityManager->remove($offre);
        $entityManager->flush();
    
        $this->addFlash('success', 'Offer deleted successfully.');
        return $this->redirectToRoute('offre_list');
    }
    
    

    #[Route('/edit_offre/{id}', name: 'app_edit_offre')]
    public function editOffre(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, int $id): Response
    {
        // Récupérer l'offre à modifier
        $offre = $offreRepository->find($id);
        
        // Si l'offre n'existe pas, afficher une erreur
        if (!$offre) {
            throw $this->createNotFoundException('Offre not found');
        }
    
        // Créer le formulaire pré-rempli avec les données de l'offre
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si une image a été téléchargée et mettre à jour l'image si nécessaire
            $imageFile = $form->get('image_offre')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
                $offre->setImageOffre($newFilename);
            }
    
            // Persister les modifications
            $entityManager->flush();
    
            // Rediriger vers la liste des offres
            return $this->redirectToRoute('offre_list');
        }
    
        // Rendre la vue avec le formulaire
        return $this->render('back/payment/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/export_offres_pdf', name: 'export_offres_pdf')]
    public function exportOffresPdf(OffreRepository $offreRepository): Response
    {
        // Récupérer toutes les offres
        $offres = $offreRepository->findAll();

        // Options pour Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instancier Dompdf
        $dompdf = new Dompdf($pdfOptions);

        // Générer le HTML du PDF
        $html = $this->renderView('back/payment/pdf.html.twig', [
            'offres' => $offres
        ]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Rendu du PDF
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Télécharger le PDF
        return new Response($dompdf->stream("offres.pdf", ["Attachment" => true]), 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}


