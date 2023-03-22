<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 07:17
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class RessourceSessionControllerTest extends WebTestCase
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

    public function providerUrlsShow()
    {
        return [
            ['/admin/session/resources/show-form', 200],
            ['/resources/show-form', 404],

        ];
    }

    /**
     * @param $url
     * @param $expectedCode
     * @dataProvider providerUrlsShow
     */
    public function testShowModule($url, $expectedCode)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('#form_ressource')->count());
        }
    }

    public function providerNew()
    {
        return [
            [1, 'div', ['session_resources' =>
                ['title' => "test6", 'url' => 'https://symfony.com/doc/current/testing.html5',
                ]],
                201],
            [1, 'div', ['session_resources' =>
                ['title' => "test7", 'url' => 'https://symfony.com/doc/current/testing.html7',
                ]],
                201],
//
            [1, 'div', ['session_resources' =>
                ['title' => "test2", 'url' => 'https://symfony.com/doc/current/testing.html5',
                ]],
                400],
            [500, 'div',
                ['resources' =>
                    ['title' => "test1",
                        'url' => 'https://symfony.com/doc/current/testing.html',
                    ]],
                404],


        ];
    }

    /**
     * @param $day_id
     * @param $type
     * @param $tab
     * @param $expectedCode
     * @dataProvider providerNew
     */
    public function testNew($day_id, $type, $tab, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/session/resources/new/' . $day_id . '/' . $type, $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testNewRenderView()
    {
        $crawler = $this->client->request('POST', '/admin/session/resources/new/1/tr');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }


    public function providerEdit()
    {
        return [
            [2, 'title test1 edit', 'https://symfony.com/doc/current/testing.html', 201],
            [2, 'title test1', '//symfony.com/doc/current/testing.html', 400],
        ];
    }

    /**
     * @param $id
     * @param $title
     * @param $url
     * @param $code
     * @dataProvider providerEdit
     */
    public function testEdit($id, $title, $url, $code)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/resources/edit-resources/' . $id);
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_resources[title]'] = $title;
        $form['session_resources[url]'] = $url;
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());

    }

    public function statusForDelete()
    {
        return [
            [2, 201],
        ];
    }

    /**
     * @dataProvider statusForDeleteError
     * @param $id
     * @param $statusCode
     */
    public function testDeleteError($id, $statusCode)
    {
        $crawler = $this->client->request('DELETE', '/admin/session/resources/delete/' . $id);
        $this->assertEquals($statusCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider statusForDelete
     * @param $id
     * @param $statusCode
     */
    public function testDelete($id, $statusCode)
    {
        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/session/resources/delete/' . $id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($statusCode, $this->client->getResponse()->getStatusCode());
    }

    public function statusForDeleteError()
    {
        return [
            [1, 400],

        ];
    }


}
