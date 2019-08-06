<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Services\RoleHelper;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    private $roleHelper;
    private $security;

    public function __construct(RoleHelper $roleHelper, Security $security)
    {
        $this->roleHelper = $roleHelper;
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'EDIT':
                if ($user === $subject) {
                    return true;
                }
                return $this->roleHelper->roleInferior($user->getRoles(), $subject->getRoles());
                break;
            case 'DELETE':
                if ($user !== $subject && $this->security->isGranted('ROLE_SUPER_ADMIN')) {
                    return true;
                }
                break;
        }

        return false;
    }
}
