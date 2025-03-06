<?php
// src/Controller/NotifController.php
namespace App\Controller;

use App\Entity\Notif;
use App\Repository\NotifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotifController extends AbstractController
{
    #[Route('/', name: 'app_notifs', methods: ['GET'])]
    public function index(NotifRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $notifs = $repo->findByUser($user);

        return $this->json([
            'notifications' => array_map(function (Notif $n) {
                return [
                    'id' => $n->getId(),
                    'type' => $n->getType(),
                    'read' => $n->isRead(),
                    'timestamp' => $n->getCreatedAt()->format('c'),
                    'postId' => $n->getPost()->getId(),
                    'user' => [
                        'name' => $n->getTriggeredBy()->getName(),
                        'pfp' => $n->getTriggeredBy()->getPfp()
                    ]
                ];
            }, $notifs)
        ]);
    }

    #[Route('/{id}/read', name: 'app_notifs_read', methods: ['POST'])]
    public function markAsRead(Notif $notif, NotifRepository $repo): JsonResponse
    {
        $notif->setIsRead(true);
        $repo->save($notif, true);
        return $this->json(['success' => true]);
    }

   
     #[Route('/notifications/mark-all-read', name: 'notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $em->getRepository(Notif::class)
            ->createQueryBuilder('n')
            ->update()
            ->set('n.read', true)
            ->where('n.user = :user AND n.read = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        return $this->json(['status' => 'success']);
    }
}