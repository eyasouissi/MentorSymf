<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Offre;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OffreRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\PaiementRepository;


final class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('front/payment/Payment.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
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

    $queryBuilder = $offreRepository->createQueryBuilder('o');

    if ($searchTerm) {
        $queryBuilder->andWhere('o.nom_offre LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    if ($sortOrder === 'asc') {
        $queryBuilder->orderBy('o.prix', 'ASC');
    } elseif ($sortOrder === 'desc') {
        $queryBuilder->orderBy('o.prix', 'DESC');
    }

    $offres = $queryBuilder->getQuery()->getResult();

    if ($request->isXmlHttpRequest()) {
        $offresData = array_map(fn($offre) => [
            'id_offre' => $offre->getIdOffre(),
            'nom_offre' => $offre->getNomOffre(),
            'image_offre' => $offre->getImageOffre() ? '/uploads/' . $offre->getImageOffre() : '',
            'prix' => $offre->getPrix(),
            'date_debut' => $offre->getDateDebut()->format('Y-m-d'),
            'date_fin' => $offre->getDateFin() ? $offre->getDateFin()->format('Y-m-d') : '',
            'description' => $offre->getDescription(),
        ], $offres);
    
        return $this->json(['offres' => $offresData]);
    }
    

    return $this->render('back/payment/list.html.twig', [
        'offres' => $offres,
        'searchTerm' => $searchTerm,
        'sortOrder' => $sortOrder,
    ]);
}


    #[Route('/front/offres', name: 'offre_list_front')]
    public function listOffresFront(OffreRepository $offreRepository, Request $request): Response
{
    $offres = $offreRepository->findAll();

    return $this->render('front/payment/Payment.html.twig', [
        'offres' => $offres,
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
        if (file_exists($imagePath)) {
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
    $offre = $offreRepository->find($id);
    
    if (!$offre) {
        throw $this->createNotFoundException('Offre not found');
    }

    $form = $this->createForm(OffreType::class, $offre);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image_offre')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads', $newFilename);
            $offre->setImageOffre($newFilename);
        }

        $entityManager->flush();

        return $this->redirectToRoute('offre_list');
    }

    return $this->render('back/payment/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

    



}
