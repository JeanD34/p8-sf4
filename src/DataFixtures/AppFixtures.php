<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 4; $i++) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setTitle('Tâche_' . $i);
            $task->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $task->setIsDone(false);
            $manager->persist($task);
        }

        $manager->flush();
    }
}
