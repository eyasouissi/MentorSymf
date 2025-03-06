<?php
// src/EventSubscriber/NotifSubscriber.php

namespace App\EventSubscriber;

use App\Entity\Notif;
use App\Event\PostLikedEvent;
use App\Event\CommentCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotifSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostLikedEvent::class => 'onPostLiked',
            CommentCreatedEvent::class => 'onCommentCreated'
        ];
    }

    public function onPostLiked(PostLikedEvent $event): void
    {
        $notif = new Notif();
        $notif->setUser($event->getPost()->getUser());
        $notif->setTriggeredBy($event->getUser());
        $notif->setType('like');
        $notif->setPost($event->getPost());

        $this->em->persist($notif);
        $this->em->flush();
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $notif = new Notif();
        $notif->setUser($event->getPost()->getUser());
        $notif->setTriggeredBy($event->getComment()->getUser());
        $notif->setType('comment');
        $notif->setPost($event->getPost());

        $this->em->persist($notif);
        $this->em->flush();
    }
}