<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NotifRepository;

#[Route('/notifications/stream')]
class NotificationStreamController extends AbstractController
{
    #[Route('/notifications/stream')]
    public function notificationStream(NotifRepository $repo): StreamedResponse
    {
        return new StreamedResponse(function() use ($repo) {
            $user = $this->getUser();
            $lastCount = -1;
            
            while (true) {
                $currentCount = $repo->countUnreadNotifications($user);
                
                if ($currentCount !== $lastCount) {
                    echo "data: " . json_encode([
                        'count' => $currentCount,
                        'timestamp' => time()
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                    $lastCount = $currentCount;
                }
                
                usleep(300000); // Check every 300ms
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]);
    }
}