<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 27/03/2019
 * Time: 09:36
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    public function testLoginPage()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testConnexionRoute()
    {
        $client = static::createClient();

        $client->request('GET', '/connexion');
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLogoutRoute()
    {
        $client = static::createClient();

        $client->request('GET', '/logout');
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLoginEmailNotFound()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Se connecter')->form();

        $form['email'] = 'notfound@test.com';
        $form['password'] = 'test12345';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter('.alert.alert-danger:contains("Email ou mot de passe incorrect")')->count());

    }

    public function testLoginInvalidPassword()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Se connecter')->form();

        $form['email'] = 'test@talan.com';
        $form['password'] = 'InvalidPwd12345';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter('.alert.alert-danger:contains("Email ou mot de passe incorrect")')->count());

    }

    public function testLoginInactiveAccount()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Se connecter')->form();

        $form['email'] = 'test.activation.3@talan.com';
        $form['password'] = 'test12345';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter('.alert.alert-warning:contains("Votre compte n\'est pas encore actif, veuillez vérifier votre boîte de réception")')->count());
    }

    public function testCheckActivationInvalidToken()
    {
        $client = static::createClient();
        $token = "TestTokenActivationInvalidToken";

        $client->request('GET', "/activation/$token");
        $client->followRedirect();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testCheckActivationValidTokenRedirectToLogin()
    {
        $client = static::createClient();
        $token = "testTokenActivation";

        $client->request('GET', "/activation/$token");
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testResendActivationValid()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "/resendActivation/4");
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter('.alert.alert-success:contains("Veuillez vérifier votre boîte de réception et confirmer votre inscription")')->count());

    }

    public function testResendActivationWithInvalidUser()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', "/resendActivation/1111111111");
        $crawler = $client->followRedirect();

        $this->assertEquals(1,
            $crawler->filter('.alert.alert-danger:contains("Utilisateur invalide")')->count());
    }

}