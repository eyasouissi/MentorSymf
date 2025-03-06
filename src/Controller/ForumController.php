<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Reply;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Form\FormFactoryInterface;

final class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function indexForum(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();
        
        $totalPosts = 0;
        foreach ($forums as $forum) {
            $totalPosts += count($forum->getPosts());
        }

        return $this->render('front/forum/forum.html.twig', [
            'forums' => $forums,
            'totalPosts' => $totalPosts,
        ]);      
    }

    #[Route('/forumB', name: 'app_forumB')]
    public function indexForumB(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();
        $topicStats = $this->calculateTopicStats($forumRepository);
        $totalPosts = array_sum(array_column($topicStats, 'postCount'));

        return $this->render('back/forum/forum.html.twig', [
            'forums' => $forums,
            'totalPosts' => $totalPosts,
            'topicStats' => $topicStats,
        ]);
    }

    #[Route('/forumB/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ForumRepository $forumRepository
    ): Response {
        $forum = new Forum();
        $forum->setCreatedAt(new \DateTime());
        $forum->setUpdatedAt(new \DateTime());

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topics = $form->get('topics')->getData();
            $forum->setTopics($topics);

            $entityManager->persist($forum);
            $entityManager->flush();

            return $this->redirectToRoute('app_forumB');
        }

        $topicStats = $this->calculateTopicStats($forumRepository);

        return $this->render('back/forum/forum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'topicStats' => $topicStats,
            'forums' => $forumRepository->findAll(),
        ]);
    }

    #[Route('/forumB/{id}', name: 'app_forum_show', methods: ['GET'])]
    public function show(
        int $id, 
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    ): Response {
        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (!$forum) {
            throw $this->createNotFoundException('Forum not found for id ' . $id);
        }

        $replyForms = [];
        foreach ($forum->getPosts() as $post) {
            foreach ($post->getComments() as $comment) {
                $reply = new Reply();
                $form = $formFactory->create('App\Form\ReplyType', $reply, [
                    'action' => $this->generateUrl('reply_create', [
                        'commentId' => $comment->getId()
                    ])
                ]);
                $replyForms[$comment->getId()] = $form->createView();
            }
        }

        return $this->render('front/forum/forum_posts.html.twig', [
            'forum' => $forum,
            'replyForms' => $replyForms,
        ]);
    }

    #[Route('/forumB/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $id,
        EntityManagerInterface $entityManager,
        ForumRepository $forumRepository
    ): Response {
        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (!$forum) {
            throw $this->createNotFoundException('Forum not found for id ' . $id);
        }

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forum->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_forumB');
        }

        $topicStats = $this->calculateTopicStats($forumRepository);

        return $this->render('back/forum/forum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
            'topicStats' => $topicStats,
            'forums' => $forumRepository->findAll(),
        ]);
    }

    #[Route('/forumB/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (!$forum) {
            throw $this->createNotFoundException('Forum not found for id ' . $id);
        }

        if ($this->isCsrfTokenValid('delete' . $forum->getId(), $request->request->get('_token'))) {
            $entityManager->remove($forum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forumB');
    }

    #[Route('/forum/export-pdf', name: 'app_forum_export_pdf')]
    public function exportPdf(ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();
        $topicStats = $this->calculateTopicStats($forumRepository);

        $html = $this->renderView('back/forum/export_pdf.html.twig', [
            'forums' => $forums,
            'topicStats' => $topicStats,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="forum_export.pdf"');

        return $response;
    }

    #[Route('/forum/{id}/increment-views', name: 'forum_increment_views', methods: ['POST'])]
    public function incrementViews(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (!$forum) {
            return new JsonResponse(['error' => 'Forum not found'], 404);
        }

        $forum->setViews($forum->getViews() + 1);
        $entityManager->flush();

        return new JsonResponse(['views' => $forum->getViews()]);
    }

    private function calculateTopicStats(ForumRepository $forumRepository): array
    {
        $forums = $forumRepository->findAll();
        $topicStats = [];
        
        foreach ($forums as $forum) {
            $topics = $forum->getTopics() ? explode(',', $forum->getTopics()) : [];
            foreach ($topics as $topic) {
                $topic = trim($topic);
                if (!isset($topicStats[$topic])) {
                    $topicStats[$topic] = [
                        'forumCount' => 0,
                        'postCount' => 0,
                    ];
                }
                $topicStats[$topic]['forumCount']++;
                $topicStats[$topic]['postCount'] += $forum->getPosts()->count();
            }
        }

        uasort($topicStats, function ($a, $b) {
            return $b['forumCount'] <=> $a['forumCount'];
        });

        return $topicStats;
    }
}