<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\PaiementRepository;
use App\Repository\OffreRepository;
use App\Service\StripeService;

final class AbonnementController extends AbstractController
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    #[Route('/abo', name: 'app_ab')]
    public function index(): Response
    {
        return $this->render('front/abonnement/index.html.twig');
    }

    #[Route('/paiement/{id}', name: 'abonnement_paiement', methods: ['GET'])]
    public function paiement(int $id, OffreRepository $offreRepository): JsonResponse
    {
        $offre = $offreRepository->find($id);

        if (!$offre) {
            return $this->json(['error' => 'Offre introuvable'], JsonResponse::HTTP_NOT_FOUND);
        }

        $products = [[
            'name' => $offre->getTitre(),
            'price' => $offre->getPrix(),
            'quantity' => 1,
            'metadata' => ['offre_id' => $offre->getIdOffre()]
        ]];

        $session = $this->stripeService->createCheckoutSession($products);

        return $this->json(['id' => $session->id]);
    }

    #[Route('/list_abonnement', name: 'app_list_abonnement')]
    public function listAbonnements(Request $request, PaiementRepository $paiementRepository): Response
    {
        $searchEmail = $request->query->get('search', '');
        $sortOrder = $request->query->get('sort', '');

        $queryBuilder = $paiementRepository->createQueryBuilder('p')
            ->leftJoin('p.idOffre', 'o')
            ->addSelect('o');

        if ($searchEmail) {
            $queryBuilder->andWhere('p.email LIKE :searchEmail')
                ->setParameter('searchEmail', '%' . $searchEmail . '%');
        }

        if ($sortOrder === 'asc') {
            $queryBuilder->orderBy('o.nomOffre', 'ASC');
        } elseif ($sortOrder === 'desc') {
            $queryBuilder->orderBy('o.nomOffre', 'DESC');
        }

        $paiements = $queryBuilder->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            $paiementsData = array_map(fn($paiement) => [
                'id_paiement' => $paiement->getIdPaiement(),
                'email' => $paiement->getEmail(),
                'offre' => $paiement->getIdOffre() ? $paiement->getIdOffre()->getNomOffre() : 'N/A',
                'card_num' => $paiement->getCardNum(),
                'date_expiration' => $paiement->getDateExpiration() ? $paiement->getDateExpiration()->format('Y-m-d') : 'N/A',
                'cvv' => $paiement->getCvv(),
            ], $paiements);

            return $this->json(['paiements' => $paiementsData]);
        }

        return $this->render('back/abonnement/list.html.twig', [
            'paiements' => $paiements,
            'searchEmail' => $searchEmail,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/abonnement/edit/{id}', name: 'app_edit_abonnement')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, PaiementRepository $paiementRepository): Response
    {
        $paiement = $paiementRepository->find($id);
        if (!$paiement) {
            throw $this->createNotFoundException("Le paiement avec l'ID $id n'existe pas.");
        }

        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_list_abonnement');
        }

        return $this->render('back/abonnement/edit.html.twig', [
            'form' => $form->createView(),
            'paiement' => $paiement,
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

    #[Route('/abonnementB', name: 'app_abonnementB')]
    public function indexB(): Response
    {
        return $this->render('back/abonnement/index.html.twig');
    }
}
