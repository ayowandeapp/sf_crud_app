<?php

namespace App\Response;

use App\Entity\Post;

class PostResponse implements ResponseInterface
{
    public function __construct(private Post $post) {}

    public function toArray(): array
    {
        return [
            "id" => $this->post->getId(),
            "title" => $this->post->getTitle(),
            "content" => $this->post->getContent(),
        ];
    }
}
