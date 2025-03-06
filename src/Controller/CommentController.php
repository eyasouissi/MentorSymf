<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Event\CommentCreatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
class CommentController extends AbstractController
{
    #[Route('/post/{id}/comment', name: 'comment_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Post $post
    ): Response {
        $user = $this->getUser();
        $post = $comment->getPost();
        if ($post->getUser()->getId() !== $this->getUser()->getId()) {
            $eventDispatcher->dispatch(new CommentCreatedEvent($post, $comment));
        }
        // Validate content
        $content = $request->request->get('content');
        if (empty($content)) {
            return $this->json([
                'success' => false,
                'message' => 'Comment content cannot be empty'
            ], Response::HTTP_BAD_REQUEST);
        }

        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setContent($content);
        $comment->setCreatedAt(new \DateTime());

        // Handle file upload
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if ($file && $file->isValid()) {
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $this->getParameter('comment_images_directory'),
                $filename
            );
            $comment->setPhoto($filename);
        }

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'user' => [
                    'username' => $user->getUsername(),
                    'pfp' => $user->getPfp() ?? 'default.jpg',
                ],
                'content' => $content,
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                'photo' => $comment->getPhoto()
            ]
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteComment(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager
    ): Response {
        // CSRF protection
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_comment_' . $comment->getId(), $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_post', [
                'forumId' => $comment->getPost()->getForum()->getId()
            ]);
        }

        // Authorization check
        $currentUser = $this->getUser();
        if (!$this->isCommentDeletable($comment, $currentUser)) {
            $this->addFlash('error', 'You are not authorized to delete this comment');
            return $this->redirectToRoute('app_post', [
                'forumId' => $comment->getPost()->getForum()->getId()
            ]);
        }

        try {
            // Delete associated photo
            $this->deleteCommentPhoto($comment);

            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting comment: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_post', [
            'forumId' => $comment->getPost()->getForum()->getId()
        ]);
    }

    private function isCommentDeletable(Comment $comment, $user): bool
    {
        return $comment->getUser() === $user || $comment->getPost()->getUser() === $user;
    }

    private function deleteCommentPhoto(Comment $comment): void
    {
        if ($comment->getPhoto()) {
            $photoPath = $this->getParameter('comment_images_directory') . '/' . $comment->getPhoto();
            if (file_exists($photoPath) && is_file($photoPath)) {
                unlink($photoPath);
            }
        }
    }
}