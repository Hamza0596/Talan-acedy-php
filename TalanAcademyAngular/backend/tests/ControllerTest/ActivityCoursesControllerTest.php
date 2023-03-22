<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 27/03/2019
 * Time: 14:54
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ActivityCoursesControllerTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->logIn();

    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');
        $this->user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('admin@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($this->user, $firewall, $this->user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testViewFormActivity()
    {
        $this->client->xmlHttpRequest('GET', '/admin/actitvity-courses/form-add-activity');
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'GET']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testAdd()
    {
        $formadd = ['activity_courses' => [
            'title' => "tire1",
            'content' => 'contenu1']];
        $this->client->xmlHttpRequest('POST', '/admin/actitvity-courses/add/1', $formadd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testAddErrorDay()
    {
        $this->client->xmlHttpRequest('POST', '/admin/actitvity-courses/add/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testAddFormError()
    {
        $formadd = ['activity_courses' => [
            'title' => "ti",
            'content' => '']];
        $this->client->xmlHttpRequest('POST', '/admin/actitvity-courses/add/1', $formadd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testGetActivity()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/actitvity-courses/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#form_edit_activity')->count());
    }

    public function testGetActivityError1()
    {
        $crawler = $this->client->request('GET', '/admin/actitvity-courses/400');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetActivityError()
    {
        $this->client->request('GET', '/admin/actitvity-courses/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testEdit()
    {
        $formEdit = ['form_edit_activity' => [
            'title' => "tire11",
            'content' => 'contenu11']];
        $this->client->xmlHttpRequest('POST', '/admin/actitvity-courses/edit/1', $formEdit);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testEditError()
    {
        $formEdit = ['form_edit_activity' => [
            'title' => "",
            'content' => 't']];
        $this->client->xmlHttpRequest('POST', '/admin/actitvity-courses/edit/1', $formEdit);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditErrorActivity()
    {
        $this->client->request('GET', '/admin/actitvity-courses/edit/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testDeleteError()
    {
        $this->client->request('DELETE', '/admin/actitvity-courses/delete/51247');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDelete()
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/actitvity-courses/delete/1');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }
}
