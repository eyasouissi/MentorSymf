<?php
namespace App\Controller;

use App\Service\StripeService;
use App\Entity\Paiement;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PaiementRepository;
use App\Repository\CoursesRepository;

use Dompdf\Dompdf;
use Dompdf\Options;

class StripeController extends AbstractController
{
    #[Route('/stripe/create-session/{id}', name: 'stripe_create_session', methods: ['GET', 'POST'])]
    public function createSession(int $id, OffreRepository $offreRepository, StripeService $stripeService, Request $request): Response
    {
        $offre = $offreRepository->find($id);
        
        if (!$offre) {
            return $this->json(['error' => 'Offre non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $session = $request->getSession();
        $session->set('offre_id', $offre->getIdOffre());

        $products = [
            [
                'name' => $offre->getNomOffre(),
                'price' => $offre->getPrix(),
                'quantity' => 1,
                'metadata' => [
                    'offre_id' => $offre->getIdOffre(),
                ],
            ]
        ];

        $checkoutSession = $stripeService->createCheckoutSession($products);

        return $this->json(['id' => $checkoutSession->id]);
    }

    #[Route('/stripe/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('front/stripe/cancel.html.twig');
    }

    #[Route('/stripe/success', name: 'stripe_success')]
    public function success(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, CoursesRepository $coursesRepository): Response
    {
        $session = $request->getSession();
        $offreId = $session->get('offre_id');
        $user = $this->getUser();
    
        if (!$offreId || !$user) {
            throw $this->createNotFoundException('Informations de paiement manquantes');
        }
    
        $offre = $offreRepository->find($offreId);
        if (!$offre) {
            throw $this->createNotFoundException('Offre non trouvée');
        }
    
        // Vérification si un paiement existe déjà
        $paiementExiste = $entityManager->getRepository(Paiement::class)->findOneBy([
            'user' => $user,
            'offre' => $offre
        ]);
    
        if (!$paiementExiste) {
            // Enregistrer le paiement
            $paiement = new Paiement();
            $paiement->setUser($user);
            $paiement->setOffre($offre);
            $paiement->setDatePaiement(new \DateTime());
    
            $entityManager->persist($paiement);
            $entityManager->flush();
        }
    
        $courses = $coursesRepository->findBy(['isPremium' => true]); 
    
        foreach ($courses as $course) {
            if ($offre->getDateDebut() <= new \DateTime() && ($offre->getDateFin() === null || $offre->getDateFin() >= new \DateTime())) {
              
                $course->setIsPremium(false); 
                $entityManager->persist($course); 
            }
        }
    
        $entityManager->flush(); // Appliquer toutes les modifications
    
        // Supprimer l'ID de l'offre de la session après le paiement
        $session->remove('offre_id');
    
        return $this->render('front/stripe/success.html.twig');
    }
    
    #[Route('/list_abonnement', name: 'app_list_abonnement')]
    public function listPaiements(PaiementRepository $paiementRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('search', '');
        $sortOption = $request->query->get('sort', 'asc'); 
        
       
        $queryBuilder = $paiementRepository->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.offre', 'o')
            ->where('u.email LIKE :search')
            ->setParameter('search', '%' . $searchQuery . '%')
            ->orderBy('p.datePaiement', $sortOption);
    
        $paiements = $queryBuilder->getQuery()->getResult();
    
        return $this->render('back/abonnement/list.html.twig', [
            'paiements' => $paiements,
            'search' => $searchQuery,
            'sort' => $sortOption,
        ]);
    }
    
    #[Route('/abonnement/delete/{id}', name: 'app_delete_abonnement')]
    public function delete(int $id, EntityManagerInterface $entityManager, PaiementRepository $paiementRepository): Response
    {
        $paiement = $paiementRepository->find($id);
        if (!$paiement) {
            throw $this->createNotFoundException("Le paiement avec l'ID $id n'existe pas.");
        }

        $entityManager->remove($paiement);
        $entityManager->flush();

        return $this->redirectToRoute('app_list_abonnement');
    }
    #[Route('/abonnement/edit/{id}', name: 'app_edit_abonnement')]
public function editPaiement(
    int $id, 
    PaiementRepository $paiementRepository, 
    EntityManagerInterface $entityManager, 
    Request $request
): Response {
    $paiement = $paiementRepository->find($id);
    
   
    if (!$paiement) {
        throw $this->createNotFoundException('Payment not found');
    }

   

    if ($request->isMethod('POST')) {
     
        $newDatePaiement = $request->request->get('datePaiement'); 

        if ($newDatePaiement) {
            $paiement->setDatePaiement(new \DateTime($newDatePaiement));
        }

      

        
        $entityManager->flush();

       
        return $this->redirectToRoute('app_list_abonnement');
    }

   
    return $this->render('back/abonnement/edit.html.twig', [
        'paiement' => $paiement,
    ]);
}
#[Route('/export_paiements_pdf', name: 'export_paiements_pdf')]
    public function exportPaiementsPdf(PaiementRepository $paiementRepository): Response
    {
       
        $paiements = $paiementRepository->findAll();

        
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        
        $dompdf = new Dompdf($pdfOptions);

        
        $html = $this->renderView('back/abonnement/pdf.html.twig', [
            'paiements' => $paiements
        ]);

        
        $dompdf->loadHtml($html);

        
        $dompdf->setPaper('A4', 'landscape');

        
        $dompdf->render();

        
        return new Response(
            $dompdf->stream("paiements.pdf", ["Attachment" => true]),
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
   

    private $entityManager;

    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/paiements/statistiques', name: 'paiements_statistiques')]
    public function statistiquesPaiementsParUtilisateur(PaiementRepository $paiementRepository): Response
    {
       
        $paiementsParUtilisateur = $paiementRepository->getPaiementsStatistiques();
    
        
        $labels = [];
        $nombrePaiements = [];
        $sommePaiements = [];
    
        foreach ($paiementsParUtilisateur as $paiement) {
            $labels[] = $paiement['email'];
            $nombrePaiements[] = $paiement['nombrePaiements'];
            $sommePaiements[] = $paiement['sommePaiements'];
        }
    
        return $this->render('back/abonnement/statistiques.html.twig', [
            'labels' => json_encode($labels),
            'nombrePaiements' => json_encode($nombrePaiements),
            'sommePaiements' => json_encode($sommePaiements),
        ]);
    }
    
        }
