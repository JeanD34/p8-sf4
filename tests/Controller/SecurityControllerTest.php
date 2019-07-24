<?php

namespace App\Tests\Controller;

use App\Tests\Utils;
use Symfony\Component\HTTPFoundation\Response;

class SecurityControllerTest extends Utils
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testNoAdminLinkWhenUser()
    {
        $crawler = $this::createUserClient();

        // Extract links from homepage and assert that admin links is not in it
        $links = $crawler->filter('a.btn-primary')->extract(['_text']);
        static::assertNotContains('Créer un utilisateur', $links);
        static::assertNotContains('Liste des utilisateurs', $links);
    }

    public function testAdminLink()
    {
        $crawler = $this::createAdminClient();

        // Extract links from homepage and assert that admin links is in it
        $links = $crawler->filter('a.btn-primary')->extract(['_text']);
        static::assertContains('Créer un utilisateur', $links);
        static::assertContains('Liste des utilisateurs', $links);

        // Assert link "Créer un utilisateur" exist and it redirects to the good route
        $createUserLink = $crawler->selectLink('Créer un utilisateur')->link();
        $uriCreateUser = $createUserLink->getUri();
        static::assertContains('/users/create', $uriCreateUser);

        // Assert link "Liste des utilisateurs" exist and it redirects to the good route
        $listUserLink = $crawler->selectLink('Liste des utilisateurs')->link();
        $uriListUser = $listUserLink->getUri();
        static::assertContains('/users', $uriListUser);
    }

    public function testInvalidCredentialsMessage()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/login');

        // Log in with wrong password
        $crawler = $this->client->submitForm('Se connecter', [
            'username' => 'Admin',
            'password' => 'Fake'
        ]);

        // Assert that flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame("Identifiants invalides.", $alert);
    }

    public function testUsernameNotFoundMessage()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/login');

        // Log in with wrong username and password
        $crawler = $this->client->submitForm('Se connecter', [
            'username' => 'Fake',
            'password' => 'Fake'
        ]);

        // Assert that flash message is displayed
        $alert = $crawler->filter('div.alert')->text();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame("Le nom d'utilisateur n'a pas pu être trouvé.", $alert);
    }

    public function testLogoutUsingLink()
    {
        $crawler = $this::createUserClient();

        // Log out using link
        $link = $crawler->selectLink('Se déconnecter')->link();
        $crawler = $this->client->click($link);

        // Assert that we are back to the login page with the button "Se connecter"
        $button = trim($crawler->filter('button')->text());
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame('Se connecter', $button);

        static::assertRouteSame('app_login');
    }

    public function testLogoutUsingRoute()
    {
        $crawler = $this::createUserClient();

        // Log out using route
        $crawler = $this->client->request('GET', '/logout');

        // Assert that we are back to the login page with the button "Se connecter"
        $button = trim($crawler->filter('button')->text());
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame('Se connecter', $button);

        static::assertRouteSame('app_login');
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
