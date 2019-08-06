<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class Utils extends WebTestCase
{
    protected static $application;

    protected $client;

    protected static $container;

    protected $entityManager;

    /**
     * Create the database | Create tables | Load Fixture
     * Create client | Get container | Get entityManager
     */
    protected function setUp()
    {
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('doctrine:fixtures:load -n');

        $this->client = static::createClient();
        self::$container     = $this->client->getContainer();
        $this->entityManager = self::$container->get('doctrine')->getManager();
    }

    /**
     * Run console command line
     *
     * @param string $command
     * 
     * @return Int 0 if if everything went fine
     */
    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    /**
     * Get Application
     *
     * @return Application
     */
    protected static function getApplication()
    {
        $client = static::createClient();
        self::$application = new Application($client->getKernel());
        self::$application->setAutoExit(false);

        return self::$application;
    }

    /**
     * Log in with ROLE_USER
     */
    protected function createUserClient()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/login');

        $crawler = $this->client->submitForm('Se connecter', [
            'username' => 'User',
            'password' => 'User340!'
        ]);
        static::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        return $crawler;
    }

    /**
     * Log in with ROLE_ADMIN
     */
    protected function createAdminClient()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/login');

        $crawler = $this->client->submitForm('Se connecter', [
            'username' => 'Admin',
            'password' => 'Admin34!'
        ]);
        static::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        return $crawler;
    }

    /**
     * Log in with ROLE_SUPER_ADMIN
     */
    protected function createSuperAdminClient()
    {
        $this->client->followRedirects();

        $crawler = $this->client->request('GET', '/login');

        $crawler = $this->client->submitForm('Se connecter', [
            'username' => 'SuperAdmin',
            'password' => 'SuperAdmin34!'
        ]);
        static::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        return $crawler;
    }

    /**
     * Drop database
     */
    protected function tearDown()
    {
        self::runCommand('doctrine:database:drop --force');
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
