<?php

namespace App\Services;

use Psr\Container\ContainerInterface;

class RoleHelper
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function roleInferior($currentUserRole, $userToEditRole)
    {

        $roles = $this->getAllRoles();

        // Example with the two users being Admin
        // end(array()) return the last value of and array, in our app the highest role name
        // $roles[end($currentUserRole)] = $roles["ROLE_ADMIN"] = 1 (See line 49)
        // $roles[end($userToEditRole)] = $roles["ROLE_ADMIN"] = 1 (See line 49)
        // Return false an user can only edit user with inferior ROLE               

        return $roles[end($currentUserRole)] > $roles[end($userToEditRole)];
    }

    public function getAllRoles()
    {

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
        return $roles = array_flip(array_unique($roles));
    }
}
