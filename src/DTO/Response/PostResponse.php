<?php

namespace App\DTO\Response;

use App\Entity\Post;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostResponse extends JsonResponse
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
