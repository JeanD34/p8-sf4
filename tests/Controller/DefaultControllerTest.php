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
        static::assertTrue($this->client->getResponse()->isRedirect('/login'));
    }

    public function testHomepageWhenConnected()
    {
        // Log in
        $crawler = $this::createUserClient();

        // Assert where are on the homepage by asserting greeting    
        $info = $crawler->filter('h1')->text();
        static::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame("Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !", $info);

        static::assertRouteSame('homepage');
    }

    public function test404WhenFakeLink()
    {
        // Assert that not existing route return 404
        $this->client->request('GET', '/-1');
        static::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
