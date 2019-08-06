<?php

namespace App\Tests\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\Utils;
use App\Services\RoleHelper;
use App\Security\Voter\UserVoter;
use App\Security\Voter\DeleteTaskVoter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class VoterTest extends Utils
{
    /**
     * Verify that anonymous user cannot edit Task
     */
    public function testDeleteTaskVoter()
    {
        $container = new Container;
        $security = new Security($container);

        $voter = new DeleteTaskVoter($security);

        $task = new Task();

        $token = new AnonymousToken('secret', 'anonymous');

        // -1 is ACCESS_DENIED
        $this->assertSame(-1, $voter->vote($token, $task, ['DELETE']));
    }

    /**
     * Verify that anonymous user cannot edit User
     */
    public function testUserVoter()
    {
        $container = new Container;
        $security = new Security($container);
        $roleHelper = new RoleHelper($container);

        $voter = new UserVoter($roleHelper, $security);

        $user = new User();

        $token = new AnonymousToken('secret', 'anonymous');

        // -1 is ACCESS_DENIED
        $this->assertSame(-1, $voter->vote($token, $user, ['EDIT']));
    }
}
