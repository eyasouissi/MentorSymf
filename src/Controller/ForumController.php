<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function indexForum(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();

        // Calculate the total posts
        $totalPosts = 0;
        foreach ($forums as $forum) {
            $totalPosts += count($forum->getPosts());  // Assuming getPosts() returns the associated posts as a collection
        }

        return $this->render('front/forum/forum.html.twig', [
            'forums' => $forums,
            'totalPosts' => $totalPosts,  // Pass totalPosts to the Twig template
        ]);
    }

    #[Route('/forumB', name: 'app_forumB')]
    public function indexForumB(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();

        // Calculate the total posts
        $totalPosts = 0;
        foreach ($forums as $forum) {
            $totalPosts += count($forum->getPosts());  // Assuming getPosts() returns the associated posts as a collection
        }

        return $this->render('back/forum/forum.html.twig', [
            'forums' => $forums,
            'totalPosts' => $totalPosts,  // Pass totalPosts to the Twig template
        ]);
    }

    #[Route('/forumB/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $forum = new Forum();
        $forum->setCreatedAt(new \DateTime());
        $forum->setUpdatedAt(new \DateTime());

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Here, we ensure that the topics input from the form is set correctly
            $topics = $form->get('topics')->getData();  // Retrieve topics field
            $forum->setTopics($topics);  // Assuming you have a setTopic() method

            $entityManager->persist($forum);
            $entityManager->flush();

            return $this->redirectToRoute('app_forumB');
        }

        return $this->render('back/forum/forum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    #[Route('/forumB/{id}', name: 'app_forum_show', methods: ['GET'])]
    public function show(Forum $forum, EntityManagerInterface $entityManager): Response
    {
        // Increment the view count
        $forum->setViews($forum->getViews() + 1);
        $entityManager->flush();

        return $this->render('back/forum/forum.html.twig', [
            'forum' => $forum,
        ]);
    }

    #[Route('/forumB/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $forum->setUpdatedAt(new \DateTime());
                $entityManager->flush();

                return $this->redirectToRoute('app_forumB');
            }
        }

        // Return the form even if it's not valid (so that validation errors show)
        return $this->render('back/forum/forum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }

    #[Route('/forumB/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $forum->getId(), $request->request->get('_token'))) {
            $entityManager->remove($forum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forumB');
    }

    #[Route('/forum/{id}', name: 'app_forum_posts', methods: ['GET'])]
    public function showPosts(Forum $forum): Response
    {
        // Render the posts related to the specific forum
        return $this->render('front/forum/forum_posts.html.twig', [
            'forum' => $forum,
        ]);
    }
}
