<?php

namespace App\Response;

use App\Entity\User;

class UserResponse implements ResponseInterface
{
    public function __construct(private User $user) {}

    public function toArray(): array
    {
        return [
            "id" => $this->user->getId(),
            "email" => $this->user->getEmail()
        ];
    }
}
