<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Forum;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Service\ContentModerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\PostLikedEvent;
use App\Event\CommentCreatedEvent;
use App\Service\TranslationService;
use App\Controller\TranslationController;

class PostController extends AbstractController
{
    #[Route('/post/{forumId}', name: 'app_post')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        ValidatorInterface $validator,
        Security $security,
        ContentModerator $moderator,
        TranslationService $translationService,
        int $forumId
    ): Response {
        $forum = $entityManager->getRepository(Forum::class)->find($forumId);
        if (!$forum) {
            throw $this->createNotFoundException('Forum not found');
        }
    
        $post = new Post();
        $form = $this->createForm(PostType::class, $post, ['edit_mode' => false]);
        $form->handleRequest($request);
    
        // Initialize template variables
        $commentForms = [];
        $posts = $postRepository->findBy(['forum' => $forum]);
        $editForms = [];
        $detectedLangs = [];
    
        // Process posts and detect languages
        foreach ($posts as $postItem) {
            $postItem->likedUsers = $postItem->getLikedByUsers()->toArray();
            $editForm = $this->createForm(PostType::class, $postItem, [
                'action' => $this->generateUrl('post_edit', ['id' => $postItem->getId()]),
                'edit_mode' => true,
                'existing_photos' => $postItem->getPhotos() ?? []
            ]);
            $editForms[$postItem->getId()] = $editForm->createView();
            $content = $postItem->getContent() ?? '';
            $detectedLangs[$postItem->getId()] = $translationService->detectLanguage($content);
        }
    
        // Handle comments
        foreach ($posts as $postInForum) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);
            
            $commentForms[$postInForum->getId()] = $commentForm->createView();
            
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $commentViolations = $moderator->checkContent($comment->getContent());
                if (!empty($commentViolations)) {
                    foreach ($commentViolations as $violation) {
                        $this->addFlash('error', $violation['message'] ?? 'Invalid comment content');
                    }
                    return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
                }
    
                $comment->setCreatedAt(new \DateTime())
                        ->setPost($postInForum);
                $entityManager->persist($comment);
                $entityManager->flush();
            }
        }
    
        // Handle main post submission
        if ($form->isSubmitted() && $form->isValid()) {
            $postViolations = $moderator->checkContent($post->getContent());
            if (!empty($postViolations)) {
                foreach ($postViolations as $violation) {
                    $this->addFlash('error', $violation['message'] ?? 'Prohibited content detected');
                }
                return $this->render('front/post/Post.html.twig', [
                    'form' => $form->createView(),
                    'commentForms' => $commentForms,
                    'forum' => $forum,
                    'posts' => $posts,
                    'editForms' => $editForms,
                    'detected_lang' => $detectedLangs,
                    'user_locale' => $this->getUser()?->getLocale() ?? 'en'
                ]);
            }
    
            $violations = $validator->validate($post);
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
                return $this->render('front/post/Post.html.twig', [
                    'form' => $form->createView(),
                    'commentForms' => $commentForms,
                    'forum' => $forum,
                    'posts' => $posts,
                    'editForms' => $editForms,
                    'user_locale' => $this->getUser()?->getLocale() ?? 'en'
                ]);
            }
    
            if ($user = $security->getUser()) {
                $post->setUser($user);
            }
    
            $post->setForum($forum)
                 ->setCreatedAt(new \DateTime())
                 ->setUpdatedAt(new \DateTime());
    
            // Handle photo uploads
            $uploadedPhotos = $form->get('photos')->getData();
            $photoNames = [];
            if ($uploadedPhotos) {
                foreach ($uploadedPhotos as $photo) {
                    $newFilename = uniqid().'.'.$photo->guessExtension();
                    try {
                        $photo->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                        $photoNames[] = $newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Error uploading photo: '.$e->getMessage());
                    }
                }
                $post->setPhotos($photoNames);
            }
    
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
        }
    
        // Final render with all parameters
        return $this->render('front/post/Post.html.twig', [
            'form' => $form->createView(),
            'commentForms' => $commentForms,
            'forum' => $forum,
            'posts' => $posts,
            'editForms' => $editForms,
            'detected_lang' => $detectedLangs,
            'user_locale' => $this->getUser()?->getLocale() ?? 'en'
        ]);
    }
    #[Route('/post/{id}/edit', name: 'post_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        ContentModerator $moderator,
        Post $post
    ): JsonResponse {
        $form = $this->createForm(PostType::class, $post, [
            'edit_mode' => true,
            'existing_photos' => $post->getPhotos() ?? []
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postViolations = $moderator->checkContent($post->getContent());
            if (!empty($postViolations)) {
                $errors = array_map(fn($v) => $v['message'] ?? 'Invalid content', $postViolations);
                return $this->json([
                    'success' => false,
                    'error' => implode(', ', $errors)
                ]);
            }

            $existingPhotos = $post->getPhotos() ?? [];
            $uploadedPhotos = $form->get('photos')->getData();
            $photosToRemove = $form->get('remove_photos')->getData() ?? [];

            foreach ($photosToRemove as $photoName) {
                if (!empty($photoName)) {
                    $photoPath = $this->getParameter('photos_directory').'/'.$photoName;
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                    $existingPhotos = array_diff($existingPhotos, [$photoName]);
                }
            }

            $newPhotoNames = [];
            if ($uploadedPhotos) {
                foreach ($uploadedPhotos as $photo) {
                    $newFilename = uniqid().'.'.$photo->guessExtension();
                    try {
                        $photo->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                        $newPhotoNames[] = $newFilename;
                    } catch (FileException $e) {
                        return $this->json([
                            'success' => false,
                            'error' => 'Error uploading photos: '.$e->getMessage()
                        ]);
                    }
                }
            }

            $post->setPhotos(array_merge(array_values($existingPhotos), $newPhotoNames))
                 ->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'post' => [
                    'content' => $post->getContent(),
                    'photos' => $post->getPhotos(),
                ]
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'error' => implode(', ', $errors)
        ]);
    }

    #[Route('/post/{forumId}/delete/{postId}', name: 'app_post_delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        int $forumId,
        int $postId
    ): Response {
        $post = $entityManager->getRepository(Post::class)->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        if ($post->getPhotos()) {
            foreach ($post->getPhotos() as $photo) {
                $photoPath = $this->getParameter('photos_directory').'/'.$photo;
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post deleted successfully!');
        return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
    }

    #[Route('/post/like/{postId}', name: 'app_post_like', methods: ['POST'])]
public function likePost(
    $postId,
    EntityManagerInterface $entityManager,
    Security $security,
    EventDispatcherInterface $eventDispatcher
): JsonResponse {
    $post = $entityManager->getRepository(Post::class)->find($postId);
    if (!$post) {
        return new JsonResponse(['error' => 'Post not found'], 404);
    }

    $user = $security->getUser();
    if (!$user) {
        return new JsonResponse(['error' => 'User not authenticated'], 401);
    }

    $wasLiked = $post->getLikedByUsers()->contains($user);

    if ($wasLiked) {
        $post->removeLikedByUser($user);
        $post->setLikes($post->getLikes() - 1);
        $liked = false;
    } else {
        $post->addLikedByUser($user);
        $post->setLikes($post->getLikes() + 1);
        $liked = true;

        // Dispatch like event only if user is not the post owner
        if ($post->getUser()->getId() !== $user->getId()) {
            $eventDispatcher->dispatch(new PostLikedEvent($post, $user));
        }
    }

    $entityManager->flush();

    // Return updated liked users data
    $likedUsers = $post->getLikedByUsers()->toArray();
    $likedUsersData = array_map(function($user) {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'pfp' => $user->getPfp(),
        ];
    }, $likedUsers);

    return new JsonResponse([
        'success' => true,
        'newLikeCount' => $post->getLikes(),
        'liked' => $liked,
        'likedUsers' => $likedUsersData
    ]);
}
    #[Route('/comment/{postId}', name: 'comment_add', methods: ['POST'])]
    public function addComment(
        Request $request,
        EntityManagerInterface $entityManager,
        ContentModerator $moderator,
        EventDispatcherInterface $eventDispatcher,
        int $postId
    ): JsonResponse {
        $post = $entityManager->getRepository(Post::class)->find($postId);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $commentContent = $request->request->get('content', '');
        
        $commentViolations = $moderator->checkContent($commentContent);
        if (!empty($commentViolations)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Comment contains prohibited terms: ' . implode(', ', 
                    array_map(fn($v) => $v['message'] ?? 'Invalid content', $commentViolations))
            ]);
        }

        $comment = new Comment();
        $comment->setPost($post)
                ->setUser($user)
                ->setCreatedAt(new \DateTime())
                ->setContent($commentContent);

        $entityManager->persist($comment);
        $entityManager->flush();

        // Dispatch comment event only if user is not the post owner
        if ($post->getUser()->getId() !== $user->getId()) {
            $eventDispatcher->dispatch(new CommentCreatedEvent($post, $comment));
        }

        return new JsonResponse([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'user' => [
                    'name' => $comment->getUser()->getName(),
                    'pfp' => $comment->getUser()->getPfp(),
                ],
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i'),
            ]
        ]);
    }

    #[Route('/forum/post/{id}', name: 'app_forum_post_show', methods: ['GET'])]
    public function show(
        Post $post,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        // Add comment form
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $forum = $post->getForum(); // Assuming Post entity has getForum() method

        // Get existing comments
        $comments = $entityManager->getRepository(Comment::class)->findBy(
            ['post' => $post],
            ['createdAt' => 'DESC']
        );

        // Get like status for current user
        $isLiked = false;
        if ($user = $security->getUser()) {
            $isLiked = $post->getLikedByUsers()->contains($user);
        }

        return $this->render('front/post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
            'comments' => $comments,
            'isLiked' => $isLiked,
            'likeCount' => $post->getLikes(),
            'forum' => $forum // Now properly defined

        ]);
    }
    }
