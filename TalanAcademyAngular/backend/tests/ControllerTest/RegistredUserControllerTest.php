<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 16/05/2019
 * Time: 16:37
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class RegistredUserControllerTest extends WebTestCase
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
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('admin@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testShowRegistred()
    {
        $crawler = $this->client->request('GET', '/admin/registred');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#select_all')->count());
    }

    public function providerResetPwd()
    {
        return [
            [['password_staff' => ['password' => ['first' => "admin1234k5",
                'second' => 'admin12345']]

            ], 200],
            [['password_staff' => ['password' => ['first' => "admin12345",
                'second' => 'admin12345']]

            ], 302]

        ];
    }

    /**
     * @param $pwd
     * @param $expectedCode
     * @dataProvider providerResetPwd
     */
    public function testResetPassword($pwd, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/RegistredResetPassword/testTokenActivation', $pwd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }


    public function testResetPasswordFail()
    {
        $this->client->request('GET', '/admin/RegistredResetPassword/test');
        $this->client->followRedirects();
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testResetPasswordRegistredMail()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/mail_registred_resetPassword/2');
        $this->assertEquals('success', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetModalMail()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/modalMail');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#add-btn')->count());
    }

    public function testSendEmail()
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/sendEmail', ['formSubject' => 'test', 'formBody' => 'test', 'data' => [6]]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTable()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [['data' => '0', 'name' => 'checkbox', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'firstName', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'email', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/registredData', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteRegistred()
    {
         $this->client->request('GET', '/deleteRegistred/16');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

}