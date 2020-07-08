<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->filter('form')->first()->form();
        $form['email'] = 'demo-0@demo.com';
        $form['password'] = 'password';
        $client->submit($form);

        $this->assertResponseRedirects('/');
        $crawler = $client->followRedirect();
        $this->assertSame(0, $crawler->filter('div.alert.alert-danger')->count());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLoggedUserIsRedirected()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'demo-0@demo.com']);
        $client->loginUser($testUser);

        $client->request('GET', '/login');
        $this->assertResponseRedirects('/');
    }
}
