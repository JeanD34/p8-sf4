<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const SUPER_ADMIN_USER_REFERENCE = 'super-admin-user';
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const USER_REFERENCE = 'user';
    public const ANON_USER_REFERENCE = 'user-anon';

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Super Admin
        $userSuperAdmin = new User;
        $userSuperAdmin->setUsername('SuperAdmin');

        $password = $this->encoder->encodePassword($userSuperAdmin, 'SuperAdmin34!');
        $userSuperAdmin->setPassword($password);

        $userSuperAdmin->setEmail('superadmin@gmail.com');
        $userSuperAdmin->setRoles(['ROLE_SUPER_ADMIN']);

        $manager->persist($userSuperAdmin);

        // Admin
        $userAdmin = new User;
        $userAdmin->setUsername('Admin');

        $password = $this->encoder->encodePassword($userAdmin, 'Admin34!');
        $userAdmin->setPassword($password);

        $userAdmin->setEmail('admin@gmail.com');
        $userAdmin->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAdmin);

        // Anonymous User
        $userAnon = new User;
        $userAnon->setUsername('UserAnon');

        $password = $this->encoder->encodePassword($userAnon, 'UserAnon34!');
        $userAnon->setPassword($password);

        $userAnon->setEmail('user-anon@gmail.com');
        $userAnon->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAnon);

        // User
        $user = new User;
        $user->setUsername('User');

        $password = $this->encoder->encodePassword($user, 'User340!');
        $user->setPassword($password);

        $user->setEmail('user@gmail.com');

        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::ADMIN_USER_REFERENCE, $userAdmin);
        $this->addReference(self::ANON_USER_REFERENCE, $userAnon);
        $this->addReference(self::USER_REFERENCE, $user);
    }
}
