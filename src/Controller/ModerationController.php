<?php
namespace App\Controller;

use App\Entity\ProhibitedWord;
use App\Form\ProhibitedWordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/detect')]
class ModerationController extends AbstractController
{
    #[Route('/', name: 'prohibited_words')]
    public function index(EntityManagerInterface $em): Response
    {
        $words = $em->getRepository(ProhibitedWord::class)->findAllOrderedByCategory();
        return $this->render('back/moderation/words.html.twig', [
            'words' => $words
        ]);
    }

    #[Route('/new', name: 'new_prohibited_word')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $word = new ProhibitedWord();
        $form = $this->createForm(ProhibitedWordType::class, $word);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($word);
            $em->flush();
            $this->addFlash('success', 'Prohibited word added successfully!');
            return $this->redirectToRoute('prohibited_words');
        }

        return $this->render('back/moderation/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'edit_prohibited_word')]
    public function edit(Request $request, ProhibitedWord $word, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProhibitedWordType::class, $word);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Prohibited word updated!');
            return $this->redirectToRoute('prohibited_words');
        }

        return $this->render('back/moderation/edit.html.twig', [
            'form' => $form->createView(),
            'word' => $word
        ]);
    }

    #[Route('/{id}', name: 'delete_prohibited_word', methods: ['POST'])]
    public function delete(Request $request, ProhibitedWord $word, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$word->getId(), $request->request->get('_token'))) {
            $em->remove($word);
            $em->flush();
            $this->addFlash('success', 'Prohibited word deleted!');
        }
        return $this->redirectToRoute('prohibited_words');
    }
}