<?php

namespace App\Response;

use App\Entity\UserProfile;

class UserProfileResponse implements ResponseInterface
{
    public function __construct(private UserProfile $userProfile) {}

    public function toArray(): array
    {
        return [
            "id" => $this->userProfile->getId(),
            "name" => $this->userProfile->getName() ?? '',
            "bio" => $this->userProfile->getBio() ?? '',
            "company" => $this->userProfile->getCompany() ?? '',
            "date_of_birth" => $this->userProfile->getDateOfBirth() ?? '',
            "user" => (new UserResponse($this->userProfile->getUser()))->toArray(),
        ];
    }
}
