<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    public function testResetPasswordRequest(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/reset-password');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->submitForm('Send password reset email', [
            'reset_password_request_form[email]' => 'demo-1@demo.com',
            'reset_password_request_form[_token]' => $crawler->filter('#reset_password_request_form__token')->attr('value'),
        ]);

        $this->assertResponseRedirects('/reset-password/check-email');
        $crawler = $client->followRedirect();
        $this->assertSame(0, $crawler->filter('div.alert.alert-danger')->count());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
