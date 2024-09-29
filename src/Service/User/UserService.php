<?php

namespace App\Service\User;

use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function updateUserProfile(UserProfile $userProfile): ?UserProfile
    {
        if (
            $updateProfile = $this->getUserProfileByUserId($userProfile->getUser()->getId())
        ) {
            $updateProfile->setName($userProfile->getName());
            $updateProfile->setBio($userProfile->getBio());
            $updateProfile->setCompany($userProfile->getCompany());
            $updateProfile->setDateOfBirth($userProfile->getDateOfBirth());
        } else {
            $updateProfile = $userProfile;
        }
        $this->em->persist($updateProfile);
        $this->em->flush();

        return $updateProfile;
    }

    public function getUserProfileByUserId(int $id)
    {
        return $this->em->getRepository(UserProfile::class)->findOneBy(['user' => $id]);
    }
}
