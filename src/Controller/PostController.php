<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Forum;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController extends AbstractController
{
    #[Route('/post/{forumId}', name: 'app_post')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        ValidatorInterface $validator,
        int $forumId
    ): Response {
        $forum = $entityManager->getRepository(Forum::class)->find($forumId);
        if (!$forum) {
            throw $this->createNotFoundException('Forum not found');
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate the content field manually
            $violations = $validator->validate($post);
            if (count($violations) > 0) {
                // Add each violation message to the flash bag
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
                return $this->render('front/post/Post.html.twig', [
                    'form' => $form->createView(),
                    'forum' => $forum,
                ]);
            }

            $post->setForum($forum);
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());

            // Handle file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
                try {
                    $photoFile->move($this->getParameter('post_photos_directory'), $photoFileName);
                    $post->setPhoto($photoFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading the photo.');
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
        }

        $posts = $postRepository->findBy(['forum' => $forum]);

        return $this->render('front/post/Post.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'posts' => $posts,
        ]);
    }

    #[Route('/post/{forumId}/edit/{id}', name: 'post_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        int $forumId,
        Post $post
    ): Response {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image removal
            if ($form->get('remove_photo')->getData()) {
                // Delete the old image from storage
                if ($post->getPhoto()) {
                    $photoPath = $this->getParameter('post_photos_directory') . '/' . $post->getPhoto();
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                    $post->setPhoto(null);
                }
            }
    
            // Handle new file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = uniqid() . '.' . $photoFile->guessExtension();
                try {
                    $photoFile->move($this->getParameter('post_photos_directory'), $photoFileName);
                    $post->setPhoto($photoFileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading the photo.');
                }
            }
    
            // Update the `updatedAt` timestamp
            $post->setUpdatedAt(new \DateTime());
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
        }
    
        return $this->render('front/post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
    
    #[Route('/post/{forumId}/delete/{postId}', name: 'app_post_delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        int $forumId,
        int $postId
    ): Response {
        $forum = $entityManager->getRepository(Forum::class)->find($forumId);
        if (!$forum) {
            throw $this->createNotFoundException('Forum not found');
        }

        $post = $entityManager->getRepository(Post::class)->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post deleted successfully!');
        return $this->redirectToRoute('app_post', ['forumId' => $forumId]);
    }

    #[Route('/post/like/{postId}', name: 'app_post_like', methods: ['POST'])]
    public function likePost($postId, EntityManagerInterface $entityManager): JsonResponse
    {
        // Fetch the post from the database
        $post = $entityManager->getRepository(Post::class)->find($postId);
        
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        // Increment the like count
        $post->setLikes($post->getLikes() + 1);
        
        // Save the changes
        $entityManager->flush();

        // Return the updated like count
        return new JsonResponse(['success' => true, 'newLikeCount' => $post->getLikes()]);
    }
}
