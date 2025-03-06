<?php
// src/Event/CommentCreatedEvent.php

namespace App\Event;

use App\Entity\Comment;
use App\Entity\Post;

class CommentCreatedEvent
{
    public function __construct(
        private Post $post,
        private Comment $comment
    ) {
    }

    public function getPost(): Post { return $this->post; }
    public function getComment(): Comment { return $this->comment; }
}