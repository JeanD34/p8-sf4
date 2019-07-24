<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_' . ($i + 1));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(false);
            $task->setUser($this->getReference(UserFixtures::ANON_USER_REFERENCE));
            $manager->persist($task);
        }

        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_' . ($i + 3));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(true);
            $task->setUser($this->getReference(UserFixtures::ANON_USER_REFERENCE));
            $manager->persist($task);
        }

        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_Admin_' . ($i + 1));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(false);
            $task->setUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($task);
        }

        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_Admin_' . ($i + 3));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(true);
            $task->setUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($task);
        }

        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_User_' . ($i + 1));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(false);
            $task->setUser($this->getReference(UserFixtures::USER_REFERENCE));
            $manager->persist($task);
        }

        for ($i = 0; $i < 2; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_User_' . ($i + 3));
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(true);
            $task->setUser($this->getReference(UserFixtures::USER_REFERENCE));
            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
