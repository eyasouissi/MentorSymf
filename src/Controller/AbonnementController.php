<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Paiement;
use App\Form\PaiementType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PaiementRepository;
use App\Repository\OffreRepository;
use App\Repository\UserRepository;


final class AbonnementController extends AbstractController
{
    
    #[Route('/abo', name: 'app_ab')]
    public function index(): Response
    {
        return $this->render('front/abonnement/index.html.twig', [
            'controller_name' => 'AbonnementController',
        ]);
    }

    #[Route('/abonnement', name: 'app_abonnement')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {

       /* $user = $this->getUser(); 

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour effectuer un abonnement.");
        }
        
        $userId = $user->getId();*/
    

        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paiement->setCardNum("1234567812345678"); // Sera haché

            $entityManager->persist($paiement);
            $entityManager->flush();

            
            return $this->redirectToRoute('offre_list_front');
        }

        return $this->render('front/abonnement/create.html.twig', [
            'f' => $form->createView(),
        ]);
    }

    #[Route('/abonnement_back', name: 'app_abonnement_back')]
    public function addB(Request $request, EntityManagerInterface $entityManager): Response
    {

        /*$user = $this->getUser(); // Récupère l'utilisateur connecté

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour effectuer un abonnement.");
        }
        
        $userId = $user->getId();*/
    

        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($paiement);
            $entityManager->flush();

            
            return $this->redirectToRoute('app_list_abonnement');
        }

        return $this->render('back/abonnement/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/list_abonnement', name: 'app_list_abonnement')]
    public function listAbonnements(Request $request, PaiementRepository $paiementRepository): Response
    {
        try {
            $searchEmail = $request->query->get('search', '');
            $sortOrder = $request->query->get('sort', '');
    
            $queryBuilder = $paiementRepository->createQueryBuilder('p')
                ->leftJoin('p.id_offre', 'o') // Corrigé en CamelCase
                ->addSelect('o');
    
            if ($searchEmail) {
                $queryBuilder->andWhere('p.yourEmail LIKE :searchEmail')
                    ->setParameter('searchEmail', '%' . $searchEmail . '%');
            }
    
            if ($sortOrder === 'asc') {
                $queryBuilder->orderBy('o.nom_offre', 'ASC'); // Corrigé en CamelCase
            } elseif ($sortOrder === 'desc') {
                $queryBuilder->orderBy('o.nom_offre', 'DESC');
            }
    
            $paiements = $queryBuilder->getQuery()->getResult();
    
            // Vérifier si c'est une requête AJAX
            if ($request->isXmlHttpRequest()) {
                $paiementsData = array_map(fn($paiement) => [
                    'id_paiement' => $paiement->getIdPaiement(),
                    'yourEmail' => $paiement->getEmail(),
                    'offre' => $paiement->getIdOffre() ? $paiement->getIdOffre()->getNomOffre() : 'N/A',
                    'card_num' => $paiement->getCardNum(),
                    'Date_expiration' => $paiement->getDateExpiration() ? $paiement->getDateExpiration()->format('Y-m-d') : 'N/A',
                    'cvv' => $paiement->getCvv(),
                ], $paiements);
    
                return $this->json(['paiements' => $paiementsData]);
            }
    
            return $this->render('back/abonnement/list.html.twig', [
                'paiements' => $paiements,
                'searchEmail' => $searchEmail,
                'sortOrder' => $sortOrder,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    


    #[Route('/abonnement/edit/{id}', name: 'app_edit_abonnement')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, PaiementRepository $paiementRepository): Response
{
    $paiement = $paiementRepository->find($id);

    if (!$paiement) {
        throw $this->createNotFoundException("Le paiement avec l'ID $id n'existe pas.");
    }

    $isCardNumberSet = !empty($paiement->getCardNum());

    $form = $this->createForm(PaiementType::class, $paiement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_list_abonnement');
    }

    return $this->render('back/abonnement/edit.html.twig', [
        'form' => $form->createView(),
        'isCardNumberSet' => $isCardNumberSet, 
        'paiement' => $paiement
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

// faire appel fel back 

    #[Route('/abonnementB', name: 'app_abonnementB')]
    public function indexB(): Response
    {
        return $this->render('back/abonnement/index.html.twig', [
            'controller_name' => 'AbonnementController',
        ]);
    }
}
