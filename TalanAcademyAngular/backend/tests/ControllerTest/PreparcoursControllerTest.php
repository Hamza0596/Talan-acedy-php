<?php

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class PreparcoursControllerTest extends WebTestCase
{
    private $client = null;
    private $route = null;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->client = static::createClient();
        $this->route = $kernel->getProjectDir();
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

    public function testShow()
    {
        $this->client->request('GET', '/admin/preparcours/show');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowFormGet(){
        $this->client->request('GET', '/admin/preparcours/show-form');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testShowForm()
    {
        $pdf = new UploadedFile(
            $this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf',
            'cv_test.pdf',
            'application/pdf',
            null,
            true
        );
        $formPreparcours = ['preparcours' => [
            'description' => 'Introduction Algorithme Initiation php/symfony',
            'pdf' => $pdf,
        ]];
        $this->client->xmlHttpRequest('POST', '/admin/preparcours/show-form', $formPreparcours);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testShowFormError()
    {
        $this->client->xmlHttpRequest('POST', '/admin/preparcours/show-form');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testActivatePreparcours(){
        $this->client->xmlHttpRequest('PATCH', '/admin/preparcours/activate/2');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testActivatePreparcoursError()
    {
        $this->client->request('PATCH', '/admin/preparcours/activate/2');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testViewPreparcoursPdf(){
        $this->client->request('PATCH', '/admin/preparcours/view-preparcours/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
