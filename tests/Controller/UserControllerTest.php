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

    public function testListAction()
    {
        $crawler = $this::createAdminClient();

        // Go to users page
        $crawler = $this->client->request('GET', '/users');
        static::assertResponseIsSuccessful();
        static::assertRouteSame('user_list');

        // Asserting User
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('Admin', $tds);
        static::assertContains('User', $tds);

        // Assert not contains other admin
        static::assertNotContains('UserAnon', $tds);
        static::assertNotContains('SuperAdmin', $tds);
    }

    public function testCreateUserAction()
    {
        $crawler = $this::createAdminClient();

        // Go to user creation page
        $crawler = $this->client->request('GET', '/users/create');
        static::assertResponseIsSuccessful();

        // Add user with form
        $form = $crawler->selectButton('Ajouter')->form();

        $form['user[username]'] = 'UserTest';
        $form['user[password][first]'] = 'UserTest34!';
        $form['user[password][second]'] = 'UserTest34!';
        $form['user[email]'] = 'user-test@gmail.com';
        $values = $form['user[roles]']->availableOptionValues();
        $form['user[roles]']->setValue($values[0]);

        $crawler = $this->client->submit($form);
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! L\'utilisateur a bien été ajouté.');

        // Assert that the new user is in the list
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('UserTest', $tds);

        // Assert user in DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'UserTest']);
        static::assertNotNull($user);
        static::assertSame('UserTest', $user->getUsername());
        static::assertSame('user-test@gmail.com', $user->getEmail());
        static::assertSame(array('ROLE_USER'), $user->getRoles());
    }

    public function testCreateActionRoleUser()
    {
        $crawler = $this::createUserClient();

        // Go to user creation page, assert that is forbidden
        $crawler = $this->client->request('GET', '/users/create');
        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditUserAction()
    {
        $crawler = $this::createAdminClient();

        // Go to the edit user page
        $crawler = $this->client->request('GET', '/users/4/edit');
        static::assertResponseIsSuccessful();

        // Edit the user with form
        $form = $crawler->selectButton('Modifier')->form();

        $form['user[username]'] = 'UserUpdate';
        $form['user[password][first]'] = 'User340!';
        $form['user[password][second]'] = 'User340!';
        $form['user[email]'] = 'user-update@gmail.com';
        $values = $form['user[roles]']->availableOptionValues();
        $form['user[roles]']->setValue($values[0]);

        $crawler = $this->client->submit($form);
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! L\'utilisateur a bien été modifié');

        // Assert that the edited user is in the list
        $tds = $crawler->filter('td')->extract(['_text']);
        static::assertContains('UserUpdate', $tds);

        // Assert user in DB
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'UserUpdate']);
        static::assertNotNull($user);
        static::assertSame('UserUpdate', $user->getUsername());
        static::assertSame('user-update@gmail.com', $user->getEmail());
        static::assertSame(array('ROLE_USER'), $user->getRoles());
    }

    /**
     * The purpose is to assert that role field is not available when user === subject
     */
    public function testEditRoleSuperAdminBeingSuperAdmin()
    {
        $crawler = $this::createSuperAdminClient();

        // Go to Admin edition page
        $crawler = $this->client->request('GET', '/users/2/edit');
        static::assertResponseIsSuccessful();

        // Assert role is available editing user with inferior role
        static::assertSelectorTextSame('#user_roles > div:nth-child(1) > label[for=user_roles_0]', 'Utilisateur');
        static::assertSelectorTextSame('#user_roles > div:nth-child(2) > label[for=user_roles_1]', 'Administrateur');

        // Go to SuperAdmin edition page
        $crawler = $this->client->request('GET', '/users/1/edit');
        static::assertResponseIsSuccessful();

        // Assert role is not available editing himself
        static::assertSelectorNotExists('#user_roles > div:nth-child(1) > label[for=user_roles_0]');
        static::assertSelectorNotExists('#user_roles > div:nth-child(2) > label[for=user_roles_1]');
    }

    public function testEditActionRoleUser()
    {
        $crawler = $this::createUserClient();

        // Go to user edition page, assert that is forbidden
        $crawler = $this->client->request('GET', '/users/3/edit');
        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test of unused (Symfony native) function of User Entity
     */
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

    public function testDeleteActionUser()
    {
        $crawler = $this::createSuperAdminClient();

        // Go to list of user
        $crawler = $this->client->request('GET', '/users');
        static::assertResponseIsSuccessful();

        // Delete an user
        $crawler = $this->client->request('GET', '/users/4/delete');
        static::assertResponseIsSuccessful();

        // Assert flash message is displayed
        static::assertSelectorTextSame('div.alert', 'Superbe ! L\'utilisateur a bien été supprimé');
    }

    public function testAccessUserPageWhenNotConnected()
    {
        // Go to edit form of an user being not authenticated
        $this->client->request('GET', '/users/3/edit');
        static::assertResponseRedirects('/login');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
