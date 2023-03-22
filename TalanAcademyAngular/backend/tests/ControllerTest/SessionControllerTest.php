<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 24/04/2019
 * Time: 12:20
 */

namespace App\Tests\ControllerTest;


use App\Entity\SessionUserData;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use App\DataFixtures\AppFixturesTest;

class SessionControllerTest extends WebTestCase
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

    public function ProviderSession()
    {
        return [
            [1, 200],
            [12, 200],
            [15, 200],
            [16, 200],
            [17, 200],
            [18, 200],
            [19, 200],
            [20, 200],
            [21, 200],
            [22, 200],
            [23, 200],

        ];
    }

    /**
     * @dataProvider ProviderSession
     */
    public function testList($id, $code)
    {
        $crawler = $this->client->request('GET', '/admin/cursus/' . $id . '/sessions');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#session-list')->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/cursus/1/sessions/new');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $this->assertEquals(1, $crawler->selectButton('ENREGISTRER')->count());
        $form['session[startDate]'] = '01-06-2020';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testAddWithInvalidstartDate()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/cursus/1/sessions/new');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $this->assertEquals(1, $crawler->selectButton('ENREGISTRER')->count());
        $form['session[startDate]'] = '01-06-2019';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testAddWithNotXmlHttpRequest()
    {
        $this->client->request('GET', '/admin/cursus/1/sessions/new');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testGetEditSessionForm()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/sessions/2/edit');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectButton('ENREGISTRER')->count());
    }

    public function testEditSessionInProgress()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/sessions/1/edit');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session[startDate]'] = '25-07-2020';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditSessionWithInvalidStartDate()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/sessions/2/edit');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session[startDate]'] = '01-06-2019';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditSession()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/sessions/2/edit');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session[startDate]'] = '01-11-2022';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }


    public function testDataTableValidationApprenti()
    {
        $draw = '1';
        $start = '0';
        $length = '5';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '4', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'module', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'course', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'repoLink', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '3', 'name' => 'note', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '4', 'name' => 'details', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '5', 'name' => 'comment', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/validationsApprenti/5', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableValidationApprentiWithoutComment()
    {
        $draw = '1';
        $start = '0';
        $length = '5';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '4', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'module', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'course', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'repoLink', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '3', 'name' => 'note', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '4', 'name' => 'details', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '5', 'name' => 'comment', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/validationsApprenti/6', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableEvaluationApprenti()
    {
        $draw = '1';
        $start = '0';
        $length = '5';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '0', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'module', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'course', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'note', 'searchable' => 'false', 'orderable' => 'false'],
            ['data' => '3', 'name' => 'comment', 'searchable' => 'false', 'orderable' => 'false'],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/evaluationApprenti/10', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }



    public function testGetEditSessionFormWithNotXmlHttpRequest()
    {
        $this->client->request('GET', '/admin/sessions/1/edit');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditSessionWithNotXmlHttpRequest()
    {
        $this->client->request('POST', '/admin/sessions/1/edit');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function providerCalendar()
    {
        return [
            [1, 200],
            [10, 200],
        ];
    }

    /**
     * @dataProvider providerCalendar
     * @param $id
     * @param $expectedCode
     */
    public function testGetCalendarDays($id, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/calendarDays/' . $id);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());

    }


    public function providerModule()
    {
        return [
            [1, 200],
            [10, 200],
        ];
    }

    /**
     * @dataProvider providerModule
     * @param $id
     * @param $expectedCode
     */
    public function testGetCalendarModules($id, $expectedCode)
    {

        $crawler = $this->client->xmlHttpRequest('GET', '/admin/calendarModule/' . $id);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function providerMentorsAppreciation()
    {
        return [
            [1, ['mentors_appreciation' => ['staff' => 9, 'comment' => 'Un niveau assez juste pour X, il faut persévérer dans le travail et les résultats augmenteront.']], 201],
        ];
    }

    /**
     * @dataProvider providerMentorsAppreciation
     * @param $id
     * @param $tab
     * @param $expectedCode
     */
    public function testMentorsAppreciation($id, $tab, $expectedCode)
    {

        $crawler = $this->client->xmlHttpRequest('POST', '/admin/sessions/mentorsAppreciation/' . $id, $tab);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider providerMentorsAppreciation
     * @param $id
     * @param $tab
     */
    public function testMentorsAppreciationWithoutRequest($id, $tab)
    {

        $crawler = $this->client->request('POST', '/admin/sessions/mentorsAppreciation/' . $id, $tab);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testListMentorsEmpty()
    {
        $this->client->request('GET', '/admin/sessions/1/mentors');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testListMentorsNotEmpty()
    {
        $this->client->request('GET', '/admin/sessions/11/mentors');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditStatusMentor()
    {
        $this->client->xmlHttpRequest('GET', '/admin/sessions/2/mentors/edit-mentor-status');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

    }

    public function testEditStatusMentorWithInvalidRequest()
    {
        $this->client->request('GET', '/admin/sessions/1/mentors/edit-mentor-status');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testSmileSlack()
    {
        $this->client->xmlHttpRequest('POST', '/admin/smileSlack/2', ['data' => 1]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditJockers()
    {
        $this->client->xmlHttpRequest('POST', '/admin/editJockers/2', ['data' => 2]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditJockersRenegat()
    {
        $this->client->xmlHttpRequest('POST', '/admin/editJockers/3', ['data' => 0]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCheckMissionFalse()
    {
        $this->client->xmlHttpRequest('POST', '/admin/checkMission/3');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCheckMissionTrue()
    {
        $this->client->xmlHttpRequest('POST', '/admin/checkMission/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testshowApprenticePerformance()
    {
        $this->client->request('GET', '/admin/apprentice_performance/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testshowApprenticePerformanceDash()
    {
        $this->client->request('GET', '/admin/apprentice_performance_dash/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeStatusQualifiedToConfirmed()
    {
        $this->client->xmlHttpRequest('POST', '/admin/changeStatusQualified/1', ['status' => SessionUserData::CONFIRMED]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeStatusQualifiedToConfirmedFail()
    {
        $this->client->request('POST', '/admin/changeStatusQualified/1', ['status' => SessionUserData::CONFIRMED]);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeStatusQualifiedToNotSelected()
    {
        $this->client->xmlHttpRequest('POST', '/admin/changeStatusQualified/1', ['status' => SessionUserData::NOTSELECTED]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function providerMentorProjectStatus()
    {
        return [
            [['subject'=>11]],
            [['subject'=>11]]
        ];
    }

    /**
     * @dataProvider providerMentorProjectStatus
     * @param $tab
     */
    public function testEditMentorProjectStatus($tab)
    {
        $this->client->xmlHttpRequest('POST', '/admin/session-mentor/1/edit-mentor-project-status',$tab);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider providerMentorProjectStatus
     * @param $tab
     */
    public function testEditMentorProjectStatusError($tab)
    {
        $this->client->request('POST', '/admin/session-mentor/1/edit-mentor-project-status',$tab);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGetDayComments(){
        $this->client->request('GET', '/admin/candidat_comments/2/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testAddRoleMentorToConfirmed()
    {
        $this->client->xmlHttpRequest('POST','/admin/addRoleMentorToConfirmed/4');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testAddRoleMentorToConfirmedError()
    {
        $this->client->request('POST','/admin/addRoleMentorToConfirmed/4');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

}
