<?php

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class TestMentorControllerTest extends WebTestCase
{

    private $client = null;
    private $user;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->logIn($this->client);
    }

    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $this->user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('mentorr@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($this->user, $firewall, $this->user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testDashboard()
    {
        $this->client->request('GET', '/mentor/dashboard');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testDataTableSessionList()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [['data' => '0', 'name' => 'Session', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'Date de début', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'Apprentis', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'Moyenne', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'Evaluation', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'Avancement', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '6', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/mentor/mentor_Session_list', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testshowApprenticePerformanceMentorDash()
    {
        $this->client->request('GET', '/mentor/mentor_apprentice_performance_dash/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testshowApprenticePerformanceMentor()
    {
        $this->client->request('GET', '/mentor/mentor_apprentice_performance/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function providerMentorsAppreciation()
    {
        return [
            [2, ['mentors_appreciation' => ['staff' => 9, 'comment' => 'Un niveau assez juste pour X...']], 201],
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

        $crawler = $this->client->xmlHttpRequest('POST', '/mentor/sessions/mentorsAppreciation/' . $id, $tab);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testShowSessionSpecificationMentor()
    {
        $crawler = $this->client->request('GET','/mentor/Sessionsubject/1/specification');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testStaticDashMentor()
    {
        $this->client->xmlHttpRequest('POST','/mentor/staticDash');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());

    }

}
