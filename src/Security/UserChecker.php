<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FailedValidationException;
use App\Service\ProcessExceptionData;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

use function Symfony\Component\Clock\now;

class UserChecker implements UserCheckerInterface
{
    /**
     * Summary of checkPreAuth
     * @param User $user
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        // if (null === $user->getBannedUntil()) {
        //     return;
        // }

        if (now()->getTimestamp() < $user->getBannedUntil()->getTimestamp()) {
            $data = new ProcessExceptionData("User banned!", 400);
            throw new FailedValidationException($data);
        }
        return;
    }

    public function checkPostAuth(UserInterface $user): void {}
}
