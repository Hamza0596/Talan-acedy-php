<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 16/05/2019
 * Time: 13:34
 */

namespace App\Tests\ControllerTest;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class TestCandidateControllerTest extends WebTestCase
{
    private $client = null;
    private $route;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->client = static::createClient();
        $this->route = $kernel->getProjectDir();
        $this->logIn($this->client);

    }

    private
    function logIn($client)
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

    public function testShowCandidate()
    {
        $crawler = $this->client->request('GET', '/admin/candidate');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#selectAll')->count());
    }

    public function testShowProfil()
    {
        $this->client->xmlHttpRequest('GET', '/admin/candidate_profil/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetModalCandidature()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/13/get_modal_candidature/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, $crawler->filter('.card')->count());
    }

    public function testCandidateCV()
    {
        $this->client->request('GET', '/admin/candidate/11/CV');
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testExportCV()
    {
        $this->client->xmlHttpRequest('POST', '/admin/exportCVs', ['data' => [11]]);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testFormRefusedFail()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/13/get_modal_candidature/1');
        $this->assertEquals(1, $crawler->filter('#formRefused')->count());
        $form = $crawler->selectButton('VALIDER')->form();
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testFormRefuseCand()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/13/get_modal_candidature/1');
        $this->assertEquals(1, $crawler->filter('#formRefused')->count());
        $form = $crawler->selectButton('VALIDER')->form();
        $form['refuse_candidature[comment]'] = 'motif de refus';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public
    function testInterviewCandidate()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/14/get_modal_candidature/2');
        $this->assertEquals(1, $crawler->filter('#formInterview')->count());
        $form = $crawler->selectButton('ainterview-btn')->form();
        $form['interview_cand[date]'] = '2019-05-16 15:50:04';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public
    function testInterviewCandidateSecondTime()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/14/get_modal_candidature/2');
        $this->assertEquals(1, $crawler->filter('#formInterview')->count());
        $form = $crawler->selectButton('ainterview-btn')->form();
        $form['interview_cand[date]'] = '2019-05-16 15:50:04';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public
    function testFormInterviewFail()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/14/get_modal_candidature/2');
        $this->assertEquals(1, $crawler->filter('#formInterview')->count());
        $form = $crawler->selectButton('ainterview-btn')->form();
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeSession()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/15/get_modal_candidature/4');
        $this->assertEquals(1, $crawler->filter('.tab-notifications')->count());
        $form = $crawler->selectButton('assign-btn')->form();
        $form['assign_candidate[Cursus]']->select(11);
        $this->client->xmlHttpRequest('POST', '/admin/changeSession', ['id' => 21]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $form['assign_candidate[Session]'];
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDesistementCandidateFail()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/14/get_modal_candidature/2');
        $this->assertEquals(1, $crawler->filter('#formDesisted')->count());
        $form = $crawler->selectButton('desisted-btn')->form();
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDesistementCandidate()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/14/get_modal_candidature/2');
        $this->assertEquals(1, $crawler->filter('#formDesisted')->count());
        $form = $crawler->selectButton('desisted-btn')->form();
        $form['refuse_candidature[comment]'] = 'motif de désistement';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public
    function testDataTable()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '5', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'checkbox', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'firstName', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'cursus', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'date', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/candidateData', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetImageProfileCandidate()
    {
        $this->client->xmlHttpRequest('GET', '/admin/candidate_profil/1');
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->find(13);
        $image = $this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_upload' . DIRECTORY_SEPARATOR . 'image_user' . DIRECTORY_SEPARATOR . $user->getImage();
        $this->assertFileExists($image);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetImageProfileCandidateError()
    {
        $this->client->xmlHttpRequest('GET', '/admin/candidate_profil/1');
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->find(13);
        $this->assertFileNotExists($this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_upload' . DIRECTORY_SEPARATOR . 'image_user' . DIRECTORY_SEPARATOR . 'notFound.img');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetModalMail()
    {
        $this->client->request('GET', '/admin/modalMailCandidate');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSendEmail()
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/sendEmailCandidate', ['formSubject' => 'test', 'formBody' => 'test', 'data' => [4]]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAssignCandidateEmptySession()
    {
        $date = new \DateTime('now');
        $dateSession = '' . $date->format('d-m-Y');
        $this->client->xmlHttpRequest('POST', '/admin/candidate/16/candidature/4/assignCandidate', ['assign_candidate' => ['Cursus' => 2, 'comment' => 'test']]);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testAssignCandidate()
    {
        $date = new \DateTime('now');
        $dateSession = '' . $date->format('d-m-Y');
        $this->client->xmlHttpRequest('POST', '/admin/candidate/16/candidature/4/assignCandidate', ['assign_candidate' => ['Cursus' => 19, 'Session' => $dateSession, 'comment' => 'test']]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testAssignCandidateException()
    {
        $date = new \DateTime('now');
        $dateSession = '' . $date->format('d-m-Y');
        $this->client->xmlHttpRequest('POST', '/admin/candidate/16/candidature/4/assignCandidate', ['assign_candidate' => ['Cursus' => 19, 'Session' => $dateSession, 'comment' => 'test']]);
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

    }

    public function testDesistementCandidateWithSessionUserData()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/candidate/16/get_modal_candidature/4');
        $this->assertEquals(1, $crawler->filter('#formDesisted')->count());
        $form = $crawler->selectButton('desisted-btn')->form();
        $form['refuse_candidature[comment]'] = 'motif de désistement';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetPreparcoursStatus()
    {
        $crawler = $this->client->request('GET', '/admin/candidate/14/get_modal_candidature_preparcours/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testValidatePreparcoursWork()
    {
        $crawler = $this->client->xmlHttpRequest('GET','/admin/validatePreparcoursWork/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testValidatePreparcoursWorkError()
    {
        $crawler = $this->client->request('GET','/admin/validatePreparcoursWork/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testRejectPreparcoursWork()
    {
        $crawler = $this->client->xmlHttpRequest('GET','/admin/rejectPreparcoursWork/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testRejectPreparcoursWorkError()
    {
        $crawler = $this->client->request('GET','/admin/rejectPreparcoursWork/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

}
