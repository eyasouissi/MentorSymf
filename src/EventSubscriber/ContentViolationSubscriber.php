<?php
namespace App\EventSubscriber;

use App\Service\ContentModerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ContentViolationSubscriber implements EventSubscriberInterface {
    private $moderator;

    public function __construct(ContentModerator $moderator) {
        $this->moderator = $moderator;
    }

    public static function getSubscribedEvents(): array {
        return [
            ControllerEvent::class => 'onControllerExecute',
        ];
    }

    public function onControllerExecute(ControllerEvent $event) {
        $request = $event->getRequest();
        
        if($request->getMethod() === 'POST' && $request->attributes->get('_route') === 'create_post') {
            $content = $request->request->get('content');
            $violations = $this->moderator->checkViolations($content);
            
            if(!empty($violations)) {
                $response = new JsonResponse([
                    'status' => 'error',
                    'violations' => $violations,
                    'message' => 'Content contains prohibited terms'
                ], 403);
                
                $event->setController(function() use ($response) {
                    return $response;
                });
            }
        }
    }
}