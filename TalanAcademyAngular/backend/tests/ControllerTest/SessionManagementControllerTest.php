<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 28/06/2019
 * Time: 19:25
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class SessionManagementControllerTest extends WebTestCase
{
    private $client = null;
    private $mentor = null;
    private $mentorNotAffected = null;



    public function setUp()
    {
        $this->client = static::createClient();
        $this->mentor = $this->client;
        $this->mentorNotAffected = $this->client;
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

    public function providerApprenticeList()
    {
        return [
            [11, 200, 1],
            [822, 404, 0],
        ];
    }

    /**
     * @param $sessionId
     * @param $expectedCode
     * @param $filter
     * @dataProvider providerApprenticeList
     */
    public function testApprenticeList($sessionId, $expectedCode, $filter)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/apprentice_list/' . $sessionId);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($filter, $crawler->filter('#Apprentices')->count());
    }

    public function testApprenticeListDash()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/apprentice_list_dash/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testApprenticeListMentor()
    {
        $this->loginMentor();
        $crawler = $this->mentor->xmlHttpRequest('GET', '/mentor/apprentice_liste/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testApprenticeListeDashMentor()
    {
        $this->loginMentor();
        $crawler = $this->mentor->xmlHttpRequest('GET', '/mentor/apprentice_liste_dash/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testApprenticeListeDashMentorWithoutSession()
    {
        $this->loginMentorNotAffected();
        $crawler = $this->mentorNotAffected->xmlHttpRequest('GET', '/mentor/apprentice_liste_dash/1');
        $this->assertEquals(302, $this->mentorNotAffected->getResponse()->getStatusCode());
    }



    public function providerGetDayComments()
    {
        return [
            [11, 200],
            [822, 404],
        ];
    }

    /**
     * @param $dayId
     * @param $expectedCode
     * @dataProvider providerApprenticeList
     */
    public function testGetDayComments($dayId, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day_comments/' . $dayId);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableCandidate()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'firstName', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'roles', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'joker', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'moyenne', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'evaluation', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->setServerParameter('HTTP_REFERER', '/admin/apprentice_list_dash/1');

        $this->client->xmlHttpRequest('POST', '/admin/apprentice_data/1', ['draw' => $draw, 'start' => $start, 'length' => $length,
            'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableCandidateConfirmed()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'firstName', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'roles', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'joker', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'moyenne', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'evaluation', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->setServerParameter('HTTP_REFERER', '/admin/apprentice_list_dash/10');

        $this->client->xmlHttpRequest('POST', '/admin/apprentice_data/10', ['draw' => $draw, 'start' => $start, 'length' => $length,
            'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableCandidateQualified()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'firstName', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'roles', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'joker', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'moyenne', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'evaluation', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->setServerParameter('HTTP_REFERER', '/admin/apprentice_list_dash/11');

        $this->client->xmlHttpRequest('POST', '/admin/apprentice_data/11', ['draw' => $draw, 'start' => $start, 'length' => $length,
            'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableCandidateUrl1()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'firstName', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'roles', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'joker', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'moyenne', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'evaluation', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->setServerParameter('HTTP_REFERER', '/admin/apprentice_list/1');

        $this->client->xmlHttpRequest('POST', '/admin/apprentice_data/1', ['draw' => $draw, 'start' => $start, 'length' => $length,
            'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableCandidateUrl2()
    {

        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'firstName', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'roles', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'joker', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'moyenne', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'evaluation', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->setServerParameter('HTTP_REFERER', '/admin/apprentice_liste_dash/1');

        $this->client->xmlHttpRequest('POST', '/admin/apprentice_data/1', ['draw' => $draw, 'start' => $start, 'length' => $length,
            'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testValidationSessionManagement()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'module', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'leçon', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'date', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'note', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'détails', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/validationSessionManagement/1', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testValidationSessionManagementWithNoSessionUser()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'module', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'leçon', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'date', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'note', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'détails', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/validationSessionManagement/5', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableEvaluation()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'Module', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'Lecon', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'Moyenne eval', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/evaluation/1', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableEvaluationWithNoSessionUser()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [
            ['data' => '0', 'name' => 'Module', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'Lecon', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'Moyenne eval', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'Min', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'Max', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/evaluation/5', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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
    private function loginMentorNotAffected()
    {
        $session = $this->mentorNotAffected->getContainer()->get('session');
        $user = $this->mentorNotAffected->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('new.email2@gmail.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->mentorNotAffected->getCookieJar()->set($cookie);
    }

}
