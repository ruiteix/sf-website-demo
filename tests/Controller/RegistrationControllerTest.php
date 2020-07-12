<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testHome(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->submitForm('Register', [
            'registration_form[email]' => 'chuck@norris.com',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->assertResponseRedirects('/');
        $crawler = $client->followRedirect();
        $this->assertSame(0, $crawler->filter('div.alert.alert-danger')->count());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
