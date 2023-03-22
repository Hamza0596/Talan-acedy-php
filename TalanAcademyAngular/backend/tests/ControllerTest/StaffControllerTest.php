<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 18/02/2020
 * Time: 15:48
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class StaffControllerTest extends WebTestCase
{
    private $clientMentor = null;
    private $client = null;
    private $user;

    public function setUp()
    {
        $this->client =  static::createClient();
        $this->clientMentor = $this->client;
        $this->logIn($this->clientMentor);
    }
    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('admin@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function logInMentor()
    {
        $session = $this->clientMentor->getContainer()->get('session');
        $this->user = $this->clientMentor->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('mentorr@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($this->user, $firewall, $this->user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->clientMentor->getCookieJar()->set($cookie);
    }


    public function testEditProfileMentor()
    {
        $this->logInMentor();
        $formMentor1 = ['staff' => [
            'firstName' => "Mentortest",
            'lastName' => 'Mentortest',
            'function' => 'Mentortest']
        ];
        $this->clientMentor->xmlHttpRequest('POST', '/mentor/profile', $formMentor1);
        $this->clientMentor->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->clientMentor->getResponse()->getStatusCode());
    }

    public function testEditProfileMentorError()
    {
        $this->logInMentor();
        $formMentor = ['staff' => [
            'firstName' => "m",
            'lastName' => 'd',
            'function' => 'a']];
        $this->clientMentor->xmlHttpRequest('POST', '/mentor/profile', $formMentor);
        $this->clientMentor->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(401, $this->clientMentor->getResponse()->getStatusCode());
    }

    public function testGetProfileMentor()
    {
        $this->logInMentor();
        $this->clientMentor->request('POST', '/mentor/profile');
        $this->assertEquals(200, $this->clientMentor->getResponse()->getStatusCode());
    }

    public function testEditProfileAdmin()
    {
        $formAdmin = ['staff' => [
            'firstName' => "admin",
            'lastName' => 'admin',
            'function' => 'administrateur']];
        $this->client->xmlHttpRequest('POST', '/admin/profile', $formAdmin);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testEditProfileAdminError()
    {
        $formAdmin1 = ['staff' => [
            'firstName' => "m",
            'lastName' => 'm',
            'function' => 'a']];
        $this->client->xmlHttpRequest('POST', '/admin/profile', $formAdmin1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }


    public function testGetProfileAdmin()
    {
        $this->client->request('GET', '/admin/profile');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
