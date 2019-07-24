<?php

namespace App\Tests;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use App\Tests\Utils;
use Symfony\Component\HTTPFoundation\Response;

class TaskControllerTest extends Utils
{
    public function setUp()
    {
        parent::setUp();
    }

    // Test symfony constraints on add/edit ?

    public function testListActionWithRoute()
    {
        $crawler = $this::createUserClient();

        // Go to task list
        $crawler = $this->client->request('GET', '/tasks');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertRouteSame('task_list');

        // Assert there are tasks
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains('Tâche_1', $links);
        static::assertContains('Tâche_2', $links);
    }

    public function testListWithButtonBackTo()
    {
        $crawler = $this::createUserClient();

        // Go to task creation page
        $crawler = $this->client->request('GET', '/tasks/create');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Click on back to task list button
        $link = $crawler->selectLink('Retour à la liste des tâches')->link();
        $crawler = $this->client->click($link);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert there are tasks
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains('Tâche_1', $links);
        static::assertContains('Tâche_2', $links);
    }

    public function testListDoneAction()
    {
        $crawler = $this::createUserClient();

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert there are tasks
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains('Tâche_3', $links);
        static::assertContains('Tâche_4', $links);
    }

    public function testCreateTask()
    {
        $crawler = $this::createUserClient();

        // Create task with form
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form();

        $form['task[title]'] = 'Tâche_Test';
        $form['task[content]'] = 'Tâche de Test';

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert task is in DB and contains what we expect
        $addedTask = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Test']);
        static::assertNotNull($addedTask);
        static::assertSame('Tâche de Test', $addedTask->getContent());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! La tâche a été bien été ajoutée.', trim($alert));

        // Assert the task in now in the list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($addedTask->getTitle(), $links);

        // Assert the bounded user is the good one
        $user = $addedTask->getUser();
        static::assertSame('User', $user->getUsername());
    }

    public function testCreateTaskActionUniqueEntity()
    {
        $crawler = $this::createUserClient();

        // Get task from DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);

        // Create task with title that already exist
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form();

        $form['task[title]'] = $task->getTitle();
        $form['task[content]'] = 'Tâche Unique Entity Test';

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert error message
        $error = $crawler->filter('span.form-error-message')->text();
        static::assertSame('Cette valeur est déjà utilisée.', trim($error));

        // Assert that there is still only one task with this title in DB
        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['title' => $task->getTitle()]);
        static::assertSame(count($tasks), 1);
    }

    public function testEditTask()
    {
        $crawler = $this::createUserClient();

        // Go to the edition page of the task id = 1
        $crawler = $this->client->request('GET', '/tasks/1/edit');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Edit the task with form
        $form = $crawler->selectButton('Modifier')->form();

        $form['task[title]'] = 'Tâche_Edition_Test';
        $form['task[content]'] = 'Tâche Edition Test';

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert task is in DB and contains what we expect
        $editedTask = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Edition_Test']);
        static::assertNotNull($editedTask);
        static::assertSame('Tâche Edition Test', $editedTask->getContent());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! La tâche a bien été modifiée.', trim($alert));

        // Assert the bounded user is still the good one
        $user = $editedTask->getUser();
        static::assertSame('UserAnon', $user->getUsername());
    }

    public function testEditTaskActionUniqueEntity()
    {
        $crawler = $this::createUserClient();

        // Get tasks from DB
        $taskToEdit = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 2]);

        // Edit task with title of an existing task
        $crawler = $this->client->request('GET', '/tasks/' . $taskToEdit->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form();

        $form['task[title]'] = $task->getTitle();
        $form['task[content]'] = 'Tâche Unique Entity Test';

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert error message
        $error = $crawler->filter('span.form-error-message')->text();
        static::assertSame('Cette valeur est déjà utilisée.', trim($error));

        // Assert that there is still only one task with this title in DB
        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['title' => $task->getTitle()]);
        static::assertSame(count($tasks), 1);
    }

    public function testToggleTaskAction()
    {
        $crawler = $this::createUserClient();

        // Assert task id = 1 is not done in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);
        static::assertNotNull($task);
        static::assertFalse($task->getIsDone());

        // Go to task list
        $crawler = $this->client->request('GET', '/tasks');

        // Assert that Tâche_1 is in task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($task->getTitle(), $links);

        // Mark as done
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! La tâche ' . $task->getTitle() . ' a bien été marquée comme faite.', trim($alert));

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert that Tâche_1 is now in done task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($task->getTitle(), $links);

        // Assert that Tâche_1 isn't in task list anymore
        $crawler = $this->client->request('GET', '/tasks');
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertNotContains($task->getTitle(), $links);

        // Assert task id = 1 is now done in DB
        $this->entityManager->close();
        $doneTask = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);
        static::assertTrue($doneTask->getIsDone());
    }

    public function testDeleteTaskActionRoleUser()
    {
        $crawler = $this::createUserClient();

        // Test deleting task with user != task.user (denied)
        $crawler = $this->client->request('GET', '/tasks/1/delete');
        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        // Assert task 11 exist in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 11]);
        static::assertNotNull($task);

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert that task is in done task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($task->getTitle(), $links);

        // Assert that task 11 is bounded to "User"
        static::assertSame('User', $task->getUser()->getUsername());

        // Assert user can delete his own tasks
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! La tâche a bien été supprimée.', trim($alert));

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert that task is no longer in done task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertNotContains($task->getTitle(), $links);

        // Assert that task no longer exist in database
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 11]);
        static::assertNull($task);
    }

    public function testDeleteTaskActionRoleAdmin()
    {
        $crawler = $this::createAdminClient();

        // Get the task and assert task exist in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);
        static::assertNotNull($task);

        // Assert that task is in task list
        $crawler = $this->client->request('GET', '/tasks');
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($task->getTitle(), $links);

        // Assert Admin is not the owner
        $user = $task->getUser()->getUsername();
        static::assertNotSame('Admin', $user);

        // UserAnon is the owner
        static::assertSame('UserAnon', $user);

        // Assert Admin can delete tasks that he's not the owner
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! La tâche a bien été supprimée.', trim($alert));

        // Go to task list
        $crawler = $this->client->request('GET', '/tasks');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert that task is no longer in task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertNotContains($task->getTitle(), $links);

        // Assert that task no longer exist in database
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 1]);
        static::assertNull($task);
    }

    public function testTaskEntityFunction()
    {
        // Get task from DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Admin_1', 'user' => 1]);

        // Get user from DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 1]);

        // Assert that getCreatedAt return an instance of datetime
        static::assertInstanceOf(DateTime::class, $task->getCreatedAt());

        // Assert that getUser return the good user
        static::assertSame($user, $task->getUser());

        // Assert that getIsDone return false
        static::assertFalse($task->getIsDone());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
