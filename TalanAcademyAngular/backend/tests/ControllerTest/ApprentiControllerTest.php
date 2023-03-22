<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/05/2019
 * Time: 16:07
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ApprentiControllerTest extends WebTestCase
{
    private $client = null;
    private $clientAprenti = null;
    private $clientAprentiPassedSession = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->clientAprenti = $this->client;
        $this->clientAprentiPassedSession = $this->client;
        $this->logIn($this->client);

    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('apprenti@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testShowDashboard()
    {
        $crawler = $this->client->request('GET', '/apprenti/dashboard');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('.startDate')->count());
    }

    public function apprenticePerformance()
    {
        $form = ['apprentice_data' => [
            'repoGit' => "https://stackoverflow.com",
            'profilSlack' => "https://stackoverflow.com"
        ]];;
        return [
            [200, $form,
            ]];
    }

    /**
     * @dataProvider apprenticePerformance
     * @param $code
     * @param $form
     */
    public function testShowDashboardForm($code, $form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/dashboard', $form);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());
    }

    public function testFetchAllYearEvents()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/apprenti/fetch_all_year_events', ['start' => '10/06/2019', 'end' => '19/07/2019']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetCurrentWeek()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/apprenti/currentWeek', ['end' => '10/07/2019 0:00']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testGetCalendarModules()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/apprenti/calendarModule');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetCalendarModulesEmptySession()
    {
        $this->logInApprentiError($this->clientAprenti);

        $crawler = $this->clientAprenti->xmlHttpRequest('GET', '/apprenti/calendarModule');
        $this->assertEquals(200, $this->clientAprenti->getResponse()->getStatusCode());
    }

    private function logInApprentiError()
    {
        $session = $this->clientAprenti->getContainer()->get('session');
        $user = $this->clientAprenti->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('apprenti1@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->clientAprenti->getCookieJar()->set($cookie);
    }

    private function logInApprentiPassedSession()
    {
        $session = $this->clientAprentiPassedSession->getContainer()->get('session');
        $user = $this->clientAprentiPassedSession->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('apprentiPassedSession@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->clientAprentiPassedSession->getCookieJar()->set($cookie);
    }

    public function testCurriculumViewer()
    {
        $crawler = $this->client->request('GET', '/apprenti/curriculum_viewer');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testSaveCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/saveCorrection/2', ['resultTrue' => [0 => 1], 'correctionComment' => 'testComment']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testSaveCorrectionInvalidRequest()
    {
        $crawler = $this->client->request('POST', '/apprenti/saveCorrection/2', ['resultTrue' => [0 => 1], 'correctionComment' => 'testComment']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerComment()
    {
        return [
            [1, 0, ['student_review' => ['comment' => "test1"]], 200],
            [3, 1, ['student_review' => ['comment' => "test1"]], 200],
            [5, 2, ['student_review' => ['comment' => "test5"]], 200],
            [7, 3, ['student_review' => ['comment' => "test5"]], 200],
            [9, 4, ['student_review' => ['comment' => "test5"]], 200],
            [11, 5, ['student_review' => ['comment' => "test5"]], 200],
            [13, 0, ['student_review' => ['comment' => "test5"]], 200],
            [14, 1, ['student_review' => ['comment' => "test5"]], 200],
            [15, 1, ['student_review' => ['comment' => "test5"]], 200],
            [16, 2, ['student_review' => ['comment' => "test5"]], 200],
            [17, 1, ['student_review' => ['comment' => "test5"]], 200],
            [18, 4, ['student_review' => ['comment' => "test5"]], 200],
            [19, 3, ['student_review' => ['comment' => "test5"]], 200],
            [20, 4, ['student_review' => ['comment' => "test5"]], 200],
            [57, 4, ['student_review' => ['comment' => "test5"]], 200],
            [58, 5, ['student_review' => ['comment' => "test5"]], 200],
            [5, 2, ['student_review' => ['comment' => ""]], 400],
            [200, 4, ['student_review' => ['comment' => "test5"]], 404],

        ];
    }

    /**
     * @param $idDay
     * @param $rating
     * @param $tab
     * @param $expectedCode
     * @dataProvider providerComment
     */
    public function testApprenticeRatingAndComment($idDay, $rating, $tab, $expectedCode)
    {
        $this->client->xmlHttpRequest('POST', '/apprenti/student_comment/' . $idDay . '/' . $rating, $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testApprenticeRatingAndCommentWithoutRequest()
    {
        $this->client->request('POST', '/apprenti/student_comment/10/5', ['student_review' => ['comment' => "test5"]]);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerStudentRating()
    {
        return [
            [1, ['onStar' => 2], 200],
            [1, ['onStar' => 5], 400],
            [2, ['onStar' => 3], 200],
            [404, ['onStar' => 5], 404],
        ];
    }

    public function providerGetCurrentDayContent()
    {
        return [
            [25, 200],
//            [19, 200],
            [404, 404],
        ];
    }

    /**
     * @param $idDay
     * @param $expectedCode
     * @dataProvider providerGetCurrentDayContent
     */
    public function testGetCurrentDayContent($idDay, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/get_current_day_content/' . $idDay, ['sessionId' => 1, 'passedSession' => null]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $idDay
     * @param $expectedCode
     * @dataProvider providerGetCurrentDayContent
     */
    public function testGetCurrentDayContentPassed($idDay, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/get_current_day_content/' . $idDay, ['sessionId' => 1, 'passedSession' => 1]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }


    public function testSessionParameter()
    {
        $crawler = $this->client->request('GET', '/apprenti/sessionParameter');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#apprenticePerformanceForm')->count());
    }

    public function sessionParameter()
    {
        $form = ['apprentice_data' => [
            'repoGit' => "https://stackoverflow.comupdated",
            'profilSlack' => "https://stackoverflow.comupdated"
        ]];
        return [
            [200, $form,
            ]];
    }


    /**
     * @dataProvider sessionParameter
     * @param $code
     * @param $form
     */
    public function testSessionParameterForm($code, $form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/apprenti/sessionParameter', $form);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());
    }

    public function testPassedSession()
    {
        $crawler = $this->client->request('GET', '/apprenti/passedSession');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#passedSessionDataTable')->count());
    }

    public function testPassedSessionDataTable()
    {
        $this->logInApprentiPassedSession($this->clientAprentiPassedSession);

        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'cursus',  'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'session',   'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'finalResult',    'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],


        ];
        $this->clientAprentiPassedSession->xmlHttpRequest('POST', '/apprenti/passedSessionDataTable', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->clientAprentiPassedSession->getResponse()->getStatusCode());

    }

    public function testAddResources()
    {
        $this->client->xmlHttpRequest('POST', '/apprenti/10/addResource/1', ['resources' => ['title' => 'title222', 'url' => "http://www.google.com.tn/test"]]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testAdd()
    {
        $this->client->request('GET', '/apprenti/curriculum_viewer');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testLoadAddResources()
    {
        $this->client->request('GET', '/apprenti/loadResources/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testDeleteResourceFail()
    {
        $this->client->request('GET', '/apprenti/deleteResource/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testDeleteResource()
    {
        $this->client->xmlHttpRequest('DELETE', '/apprenti/deleteResource/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testListCorrection()
    {
        $this->client->request('GET', '/apprenti/corrections');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateRessourceToApprove()
    {
        $this->client->xmlHttpRequest('POST', '/apprenti/10/addResource/1', ['resources' => ['title' => 'title222', 'url' => "http://www.google.com.tn/test"]]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetRatingForm()
    {
        $this->client->xmlHttpRequest('GET', '/apprenti/rating/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSendReclamation()
    {
        $this->client->xmlHttpRequest('POST', '/apprenti/sendReclamation', ['remarque' => 'remarqueTest', 'day' => 58]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testSendReclamationError()
    {
        $this->client->request('POST', '/apprenti/sendReclamation', ['remarque' => 'remarqueTest', 'day' => 58]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

}
