<?php

namespace App\Tests\ControllerTest;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ResourcesControllerTest extends WebTestCase
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
            ['/admin/resources/show-form', 200],
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
            [1, ['resources' => ['title' => "test1", 'url' => 'https://symfony.com/doc/current/testing.html']], 200],
            [1, ['resources' => ['title' => "test", 'url' => 'https://symfony.com/doc/currenpollt/testing.html']], 200],
            [1, ['resources' => ['title' => "testResource", 'url' => 'https://symfony.com/doc/current/testing.html']], 400],
            [500, ['resources' => ['title' => "test1", 'url' => 'https://symfony.com/doc/current/testing.html']], 404],


        ];
    }

    /**
     * @param $tab
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerNew
     */
    public function testNew($day_id, $tab, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/resources/new/' . $day_id, $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function providerAdd()
    {
        return [
            [1, ['resources' => ['title' => "test1ddddmm", 'url' => 'https://symfony.com/doc/current/testing.html']], 201],
            [1, ['resources' => ['title' => "test1ddddfff", 'url' => 'https://symfony.com/doc/current/testing.htmlfff']], 201],
            [1, ['resources' => ['title' => "test1ddddfefzef", 'url' => 'https://symfony.com/doc/current/testing.htmleez']], 201],
            [500, ['resources' => ['title' => "test1ddd", 'url' => 'https://symfony.com/doc/current/testing.html']], 404],
            [1, ['resources' => ['title' => "test1ddd", 'url' => 'https://symfony.com/doc/current/testing.html']], 400],

        ];
    }

    public function testNewWithNotXmlHttpRequest()
    {
        $this->client->request('POST', '/admin/resources/new/3', ['resources' => ['title' => "test1dddd", 'url' => 'https://symfony.com/doc/current/testing.html', 'ResourcesOwner' => 'Administrateur']]);

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
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/resources/edit-resources/' . $id);
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['resources[title]'] = $title;
        $form['resources[url]'] = $url;
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());

    }

    public function testDeleteWithNotXmlHttpRequest()
    {

        $this->client->request('DELETE', '/admin/resources/delete/2');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function statusForDelete()
    {
        return [
            [2, 201],
            [1111, 404],
        ];
    }

    /**
     * @dataProvider statusForDelete
     * @param $id
     * @param $statusCode
     */
    public function testDelete($id, $statusCode)
    {

        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/resources/delete/' . $id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($statusCode, $this->client->getResponse()->getStatusCode());
    }
}
