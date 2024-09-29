<?php

namespace App\Response;

use App\Entity\Comment;

class CommentResponse implements ResponseInterface
{
    public function __construct(private Comment $comment) {}

    public function toArray(): array
    {
        return [
            "id" => $this->comment->getId(),
            "title" => $this->comment->getText(),
            "post" => (new PostResponse($this->comment->getPost()))->toArray(),
        ];
    }
}
