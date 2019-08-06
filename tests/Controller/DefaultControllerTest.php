<?php

namespace App\Tests\Controller;

use App\Tests\Utils;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends Utils
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testHomepageRedirection()
    {
        // Log in
        $this->client->request('GET', '/');

        // If you're not connected, you are redirected to /login page
        static::assertResponseRedirects('/login');
    }

    public function testHomepageWhenConnected()
    {
        // Log in
        $this::createUserClient();

        // Assert where are on the homepage by asserting greeting    
        static::assertResponseIsSuccessful();
        static::assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");

        // Route is 'homepage'
        static::assertRouteSame('homepage');
    }

    public function test404WhenFakeLink()
    {
        // Assert that not existing route return 404
        $this->client->request('GET', '/-1');
        static::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
