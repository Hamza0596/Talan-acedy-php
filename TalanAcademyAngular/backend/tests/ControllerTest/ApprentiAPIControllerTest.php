<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 08/06/2020
 * Time: 11:54
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use FOS\RestBundle\Tests\Functional\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ApprentiAPIControllerTest extends WebTestCase
{


    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->logIn($this->client);
    }

    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('apprenti@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function bilanApprentice()
    {
        $request = $this->client->post('/api/apprenti/bilan/1', null);
        $response = $request->send();
        $this->assertEquals(201, $response->getStatusCode());
//        $this->assertTrue($response->hasHeader('Location'));
//        $data = json_decode($response->getBody(true), true);
//        $this->assertArrayHasKey('nickname', $data);

    }
}
