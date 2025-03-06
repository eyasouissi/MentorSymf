<?php

namespace App\Controller;

use App\Entity\Reply;
use App\Form\ReplyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReplyController extends AbstractController
{
    #[Route('/reply/{commentId}', name: 'reply_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        int $commentId
    ): Response {
        $reply = new Reply();
        $form = $this->createForm(ReplyType::class, $reply);
        $form->handleRequest($request);

        $comment = $entityManager->getRepository(Comment::class)->find($commentId);
        
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setUser($this->getUser());
            $reply->setComment($comment);
            
            $entityManager->persist($reply);
            $entityManager->flush();

            return $this->redirectToRoute('forum_show', [
                'id' => $reply->getComment()->getPost()->getForum()->getId(),
                '_fragment' => 'post-' . $reply->getComment()->getPost()->getId()
            ]);
        }

        // Handle form errors
        return $this->redirectToRoute('forum_show', [
            'id' => $comment->getPost()->getForum()->getId(),
            '_fragment' => 'post-' . $comment->getPost()->getId()
        ]);
    }

    #[Route('/reply/{id}/delete', name: 'reply_delete', methods: ['POST'])]
    #[IsGranted('DELETE', subject: 'reply')]
    public function delete(
        Request $request,
        Reply $reply,
        EntityManagerInterface $entityManager
    ): Response {
        $post = $reply->getComment()->getPost();
        $forumId = $post->getForum()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$reply->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reply);
            $entityManager->flush();
        }

        return $this->redirectToRoute('forum_show', [
            'id' => $forumId,
            '_fragment' => 'post-' . $post->getId()
        ]);
    }
}