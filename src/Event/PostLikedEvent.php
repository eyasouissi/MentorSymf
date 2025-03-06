<?php
namespace App\Event;

use App\Entity\Post;
use App\Entity\User;

class PostLikedEvent
{
    public function __construct(
        private Post $post,
        private User $user
    ) {}

    public function getPost(): Post { return $this->post; }
    public function getUser(): User { return $this->user; }
}