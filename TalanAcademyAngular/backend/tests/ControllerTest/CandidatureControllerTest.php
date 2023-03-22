<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 24/05/2019
 * Time: 12:23
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class CandidatureControllerTest extends WebTestCase
{
    private $client = null;


    public function setUp()
    {
        $this->client = static::createClient();
        $this->logIn($this->client);

    }

    private
    function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('candidate.account2@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testCandidatureStateShow()
    {
        $this->client->request('GET', '/candidate/candidature/1/states');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}