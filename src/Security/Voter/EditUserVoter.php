<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Psr\Container\ContainerInterface;

class EditUserVoter extends Voter
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['EDIT'])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Get array with all the availables roles
        $roleHierarchy =  $this->container->getParameter('security.role_hierarchy.roles');

        // Create an increasing array of roles
        $roles = [];
        foreach ($roleHierarchy as $key => $value) {
            foreach ($value as $key => $childRole) {
                $roles[] = $childRole;
            }
        }
        foreach ($roleHierarchy as $parentRole => $value) {
            $roles[] = $parentRole;
        }

        // array_unique remove duplicate value
        // array_flip to have keys becoming values in the array (Values become numbers so ROLE have numerical value and can be compare)
        // Example : "ROLE_USER" => 0, "ROLE_ADMIN" => 1
        $roles = array_flip(array_unique($roles));

        switch ($attribute) {
            case 'EDIT':
                if ($user === $subject) {
                    return true;
                }
                // Example with the two users being Admin
                // $roles[$user->getRoles()[0]] = $roles["ROLE_ADMIN"] = 1 (See line 44)
                // $roles[$subject->getRoles()[0]] = $roles["ROLE_ADMIN"] = 1 (See line 44)
                // Return false an user can only edit user with inferior ROLE
                return $roles[$user->getRoles()[0]] > $roles[$subject->getRoles()[0]];
                break;
        }

        return false;
    }
}
