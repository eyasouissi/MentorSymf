<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getIsRestricted()) {
            throw new CustomUserMessageAccountStatusException('User  is restricted.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // No action needed
    }
}