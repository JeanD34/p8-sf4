<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Utils;
use Symfony\Component\HTTPFoundation\Response;
use App\Entity\Task;

class UserControllerTest extends Utils
{
    public function setUp()
    {
        parent::setUp();
    }

    // Test symfony constraints on add/edit ?

    public function testListAction()
    {
        $crawler = $this::createAdminClient();

        // Go to users page
        $crawler = $this->client->request('GET', '/users');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertRouteSame('user_list');

        // Asserting User
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('Admin', $tds);
        static::assertContains('UserAnon', $tds);
        static::assertContains('User', $tds);
    }

    public function testCreateAction()
    {
        $crawler = $this::createAdminClient();

        // Go to user creation page
        $crawler = $this->client->request('GET', '/users/create');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Add user with form
        $form = $crawler->selectButton('Ajouter')->form();

        $form['user[username]'] = 'UserTest';
        $form['user[password][first]'] = 'UserTest34!';
        $form['user[password][second]'] = 'UserTest34!';
        $form['user[email]'] = 'user-test@gmail.com';
        $form['user[roles]']->select('ROLE_USER');

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! L\'utilisateur a bien été ajouté.', trim($alert));

        // Assert that the new user is in the list
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('UserTest', $tds);

        // Assert user in DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'UserTest']);
        static::assertNotNull($user);
        static::assertSame('UserTest', $user->getUsername());
        static::assertSame('user-test@gmail.com', $user->getEmail());
        static::assertSame(array('ROLE_USER'), $user->getRoles());

        // Tester contraintes
    }

    public function testCreateActionRoleUser()
    {
        $crawler = $this::createUserClient();

        // Go to user creation page, assert that is forbidden
        $crawler = $this->client->request('GET', '/users/create');
        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testEditAction()
    {
        $crawler = $this::createAdminClient();

        // Go to the edit user page
        $crawler = $this->client->request('GET', '/users/3/edit');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Edit the user with form
        $form = $crawler->selectButton('Modifier')->form();

        $form['user[username]'] = 'UserUpdate';
        $form['user[password][first]'] = 'User340!';
        $form['user[password][second]'] = 'User340!';
        $form['user[email]'] = 'user-update@gmail.com';
        $form['user[roles]']->select('ROLE_USER');

        $crawler = $this->client->submit($form);
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Assert flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame('Superbe ! L\'utilisateur a bien été modifié', trim($alert));

        // Assert that the edited user is in the list
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('UserUpdate', $tds);

        // Assert user in DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'UserUpdate']);
        static::assertNotNull($user);
        static::assertSame('UserUpdate', $user->getUsername());
        static::assertSame('user-update@gmail.com', $user->getEmail());
        static::assertSame(array('ROLE_USER'), $user->getRoles());

        // Tester contraintes (unique, blank...)
    }

    public function testEditActionRoleUser()
    {
        $crawler = $this::createUserClient();

        // Go to user edition page, assert that is forbidden
        $crawler = $this->client->request('GET', '/users/3/edit');
        static::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserEntityFunction()
    {
        // Get the user from DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'Admin']);
        static::assertNotNull($user);

        // Get user's task, asserting there is 4
        $tasks = $user->getTasks();
        static::assertSame(4, count($tasks));

        // Create new task, add user to task, assert user is the good one
        $task = new Task;
        $user->addTask($task);
        static::assertSame($user, $task->getUser());

        // Remove user from task, assert user is null now
        $user->removeTask($task);
        static::assertNull($task->getUser());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
