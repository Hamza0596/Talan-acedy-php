<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 31/07/2019
 * Time: 13:56
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ApprentiControllerCorrectionTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->logIn($this->client);

    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('apprentiCorrection1@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    public function testSaveCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/saveCorrection/3', ['resultTrue' => [0 => 1], 'correctionComment' => 'testComment']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }
}
