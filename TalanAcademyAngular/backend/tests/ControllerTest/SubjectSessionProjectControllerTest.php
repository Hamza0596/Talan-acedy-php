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

class SubjectSessionProjectController extends WebTestCase
{
    private $client = null;
    private $mentor = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->mentor = $this->client;
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
    private function loginMentor()
    {
        $session = $this->mentor->getContainer()->get('session');
        $user = $this->mentor->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('mentorr@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->mentor->getCookieJar()->set($cookie);
    }

    public function testSubjectsProjectList()
    {
        $this->client->request('GET', '/admin/subjectsSessionProject/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testSubjectsProjectListMentor()
    {
        $this->loginMentor();
        $this->mentor->request('GET', '/mentor/subjectsSessionProject/1');
        $this->assertEquals(200, $this->mentor->getResponse()->getStatusCode());
    }

    public function testAddSubject()
    {
        $crawler = $this->client->request('GET', '/admin/addSessionSubject/32');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_project_subject[name]'] = 'projet symfony';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }


    public function testEditSubject()
    {
        $crawler = $this->client->request('GET', '/admin/SessionProjectSubject/11/edit');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_project_subject[name]'] = 'projet';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteSubject()
    {
        $this->client->xmlHttpRequest('GET', '/admin/sessionSubject/5/delete');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testDeleteSubjectFail()
    {
        $this->client->request('GET', '/admin/sessionSubject/4/delete');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testAddSubjectSpecification()
    {
        $crawler = $this->client->request('GET', '/admin/Sessionsubject/11/specification');
        $form = $crawler->selectButton('AJOUTER')->form();
        $form['session_specification_subject[specification]'] = 'specification';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testAddSubjectSpecificationFail()
    {
        $crawler = $this->client->request('GET', '/admin/Sessionsubject/1/specification');
        $form = $crawler->selectButton('MODIFIER')->form();
        $form['session_specification_subject[specification]'] = '';
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function providerValidDayCheck()
    {
        return [
            [32, 1, ['check' => 'true'], 200],
            [32, 1, ['check' => 'false'], 200],
            [32, 2, ['check' => 'false'], 200],
            [32, 4, ['check' => 'true'], 200],
            [32, 7, ['check' => 'true'], 200],
            [32, 3, ['check' => 'true'], 200],
        ];
    }

    /**
     * @param $module_id
     * @param $subject_id
     * @param $check_value
     * @param $expectedCode
     * @dataProvider providerValidDayCheck
     */
    public function testToggleSubjectVisibility($module_id, $subject_id, $check_value, $expectedCode)
    {
        $this->client->xmlHttpRequest('POST', '/admin/SessionSubjectVisibility/' . $module_id . '/' . $subject_id, $check_value);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testValidDayCheckWithoutRequest()
    {
        $this->client->request('POST', '/admin/SessionSubjectVisibility/6/10', ['check' => 'true']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerAssignement()
    {
        return [
            [['selectedItems' => [10]]],
            [['selectedItems' => [11]]],
        ];
    }

    /**
     * @param $selectedItems
     * @dataProvider providerAssignement
     */
    public function testAssignement($selectedItems)
    {
        $this->client->xmlHttpRequest('POST', '/admin/assignement/3', $selectedItems);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());


    }

    public function testErrorAssignement()
    {
        $this->client->request('POST', '/admin/assignement/3');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $selectedItems
     * @dataProvider providerAssignement
     */
    public function testDesassignement($selectedItems)
    {
        $this->client->xmlHttpRequest('POST', '/admin/desassignement/3', $selectedItems);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testErrorDesassignement()
    {
        $this->client->request('POST', '/admin/desassignement/3');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testDaySubjectList()
    {
        $this->client->request('GET','/admin/session/subject/day/list/1');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
    }

    public function testDaySubjectListMentor()
    {
        $this->loginMentor($this->mentor);
        $this->client->request('GET','/mentor/session/subject/day/list/1');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
    }

   public function testGetSessionSubjectDayContent()
    {
        $crawler = $this->client->xmlHttpRequest('GET','/session/subject/day/content/1');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
    }
    public function testGetSessionSubjectDayContentError()
    {
        $crawler = $this->client->request('GET','/session/subject/day/content/1');
        $this->assertEquals(400,$this->client->getResponse()->getStatusCode());
    }

    public function content()
    {
        $form = ['session_subject_day_content' => [
            'content' => "test content"],
        ];;
        return [
            [ $form,
            ]];
    }

    /**
     * @dataProvider content
     * @param $form
     */
    public function testAddSessionSubjectDayContent($form)
    {
        $crawler = $this->client->xmlHttpRequest('POST','/admin/session/subject/day/addContent/1',$form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testErrorAddSessionSubjectDayContent()
    {
        $crawler = $this->client->request('POST','/admin/session/subject/day/addContent/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
