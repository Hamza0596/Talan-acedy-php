<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 04/09/2019
 * Time: 08:31
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class SubjectProjectControllerTest extends WebTestCase
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

    public function testSubjectsProjectList()
    {
        $this->client->request('GET', '/admin/subjectsProject/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAddSubject()
    {
        $crawler = $this->client->request('GET', '/admin/addSubject/2');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['subject_project[name]'] = 'projet symfony';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }


    public function testEditSubject()
    {
        $crawler = $this->client->request('GET', '/admin/ProjectSubject/1/edit');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['subject_project[name]'] = 'projet';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteSubject()
    {
        $this->client->xmlHttpRequest('GET', '/admin/subject/25/delete');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testDeleteSubjectFail()
    {
        $this->client->request('GET', '/admin/subject/4/delete');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testAddSubjectSpecification()
    {
        $crawler = $this->client->request('GET', '/admin/subject/1/specification');
        $form = $crawler->selectButton('AJOUTER')->form();
        $form['specification_subject[specification]'] = 'specification';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testAddSubjectSpecificationFail()
    {
        $crawler = $this->client->request('GET', '/admin/subject/1/specification');
        $form = $crawler->selectButton('MODIFIER')->form();
        $form['specification_subject[specification]'] = '';
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }


}