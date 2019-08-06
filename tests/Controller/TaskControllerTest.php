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

    public function testListActionWithRoute()
    {
        $crawler = $this::createUserClient();

        // Go to task list
        $crawler = $this->client->request('GET', '/tasks');
        static::assertResponseIsSuccessful();
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
        static::assertResponseIsSuccessful();

        // Click on back to task list button
        $link = $crawler->selectLink('Retour à la liste des tâches')->link();
        $crawler = $this->client->click($link);
        static::assertResponseIsSuccessful();

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
        static::assertResponseIsSuccessful();

        // Assert there are tasks
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains('Tâche_3', $links);
        static::assertContains('Tâche_4', $links);
    }

    public function testShowAction()
    {
        $crawler = $this::createUserClient();

        // Go to show task page
        $crawler = $this->client->request('GET', '/tasks/1/show');
        static::assertResponseIsSuccessful();

        // Assert it's "Tâche 1"
        static::assertSelectorTextSame('small', 'Tâche_1');
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
        static::assertResponseIsSuccessful();

        // Assert task is in DB and contains what we expect
        $addedTask = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Test']);
        static::assertNotNull($addedTask);
        static::assertSame('Tâche de Test', $addedTask->getContent());

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche a été bien été ajoutée.');

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
        static::assertResponseIsSuccessful();

        // Assert error message
        static::assertSelectorTextSame('span.form-error-message', 'Cette valeur est déjà utilisée.');

        // Assert that there is still only one task with this title in DB
        $tasks = $this->entityManager->getRepository(Task::class)->findBy(['title' => $task->getTitle()]);
        static::assertSame(count($tasks), 1);
    }

    public function testEditTask()
    {
        $crawler = $this::createUserClient();

        // Go to the edition page of the task id = 1
        $crawler = $this->client->request('GET', '/tasks/1/edit');
        static::assertResponseIsSuccessful();

        // Edit the task with form
        $form = $crawler->selectButton('Modifier')->form();

        $form['task[title]'] = 'Tâche_Edition_Test';
        $form['task[content]'] = 'Tâche Edition Test';

        $crawler = $this->client->submit($form);
        static::assertResponseIsSuccessful();

        // Assert task is in DB and contains what we expect
        $editedTask = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Edition_Test']);
        static::assertNotNull($editedTask);
        static::assertSame('Tâche Edition Test', $editedTask->getContent());

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche a bien été modifiée.');

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
        static::assertResponseIsSuccessful();

        // Assert error message
        static::assertSelectorTextSame('span.form-error-message', 'Cette valeur est déjà utilisée.');

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
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche ' . $task->getTitle() . ' a bien été marquée comme faite.');

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertResponseIsSuccessful();

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
        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // Assert task 11 exist in DB
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['id' => 11]);
        static::assertNotNull($task);

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertResponseIsSuccessful();

        // Assert that task is in done task list
        $links = $crawler->filter('a')->extract(['_text']);
        static::assertContains($task->getTitle(), $links);

        // Assert that task 11 is bounded to "User"
        static::assertSame('User', $task->getUser()->getUsername());

        // Assert user can delete his own tasks
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche a bien été supprimée.');

        // Go to done task list
        $crawler = $this->client->request('GET', '/tasks/done');
        static::assertResponseIsSuccessful();

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
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche a bien été supprimée.');

        // Go to task list
        $crawler = $this->client->request('GET', '/tasks');
        static::assertResponseIsSuccessful();

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
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche_Admin_1', 'user' => 2]);

        // Get user from DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 2]);

        // Assert that getCreatedAt return an instance of datetime
        static::assertInstanceOf(DateTime::class, $task->getCreatedAt());

        // Assert that getUser return the good user
        static::assertSame($user, $task->getUser());

        // Assert that getIsDone return false
        static::assertFalse($task->getIsDone());
    }

    public function testAccessTaskEditionWhenNotConnected()
    {
        // Go to edit form of an user being not authenticated
        $this->client->request('GET', '/tasks/1/edit');
        static::assertResponseRedirects('/login');
    }

    public function testAccessTaskDeletionSuperAdmin()
    {
        $crawler = $this::createSuperAdminClient();

        // Go to edit form of an user being not authenticated
        $crawler = $this->client->request('GET', '/tasks/1/delete');
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! La tâche a bien été supprimée.');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
