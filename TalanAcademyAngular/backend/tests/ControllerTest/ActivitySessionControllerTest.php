<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 08:35
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ActivitySessionControllerTest extends WebTestCase
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
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('admin@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testViewFormActivity()
    {
        $crawler = $this->client->request('GET', '/admin/actitvity-session/form-add-activity');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#form_activity')->count());
    }

    public function providerAdd()
    {
        return [
            [1,
                ['session_activity' =>
                    ['title' => "test activity 1",
                        'content' => 'content activity test',]
                ]
                , 201],
            [1,
                ['session_activity' =>
                    ['title' => "test activity 2",
                        'content' => 'content activity test',]
                ]
                , 201],
            [1,
                ['session_activity1' =>
                    ['title' => "test activity 2",
                        'content' => 'content activity test',]
                ]
                , 400],
            [1,
                ['session_activity' =>
                    ['title' => "",
                        'content' => '',]
                ]
                , 400],
            [500,
                ['session_activity' =>
                    ['title' => "test activity 2",
                        'content' => 'content activity test',]
                ]
                , 404],


        ];
    }

    /**
     * @param $tab
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerAdd
     */
    public function testAdd($day_id, $tab, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', 'admin/actitvity-session/add/' . $day_id, $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testGetActivity()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/actitvity-session/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#form_edit_activity')->count());

    }

    public function testGetActivityError()
    {
        $this->client->request('GET', '/admin/actitvity-session/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerEdit()
    {
        return [
            [1,
                ['form_edit_session_activity' =>
                    ['title' => "test1 edit",
                        'content' => 'activity content',
                    ]
                ]
                , 201],
            [1, ['form_edit_session_activity' => ['title' => "", 'content' => 'dd']], 400],
        ];
    }

    /**
     * @param $tab
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerEdit
     */
    public function testEdit($day_id, $tab, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/actitvity-session/edit/' . $day_id, $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testEditError()
    {
        $crawler = $this->client->request('POST', '/admin/actitvity-session/edit/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function dataProviderDelete()
    {
        return [
            [2, 201],
        ];
    }

    /**
     * @dataProvider dataProviderDelete
     * @param $id
     * @param $statusCode
     */
    public function testDelete($id, $statusCode)
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/actitvity-session/delete/' . $id);
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals($statusCode, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteError()
    {
        $this->client->request('DELETE', '/admin/actitvity-session/delete/1');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testDeleteErrorNotFound()
    {
        $this->client->request('DELETE', '/admin/actitvity-session/delete/400');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}