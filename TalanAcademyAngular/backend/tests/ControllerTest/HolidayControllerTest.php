<?php

namespace App\Tests\ControllerTest;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;


class HolidayControllerTest extends WebTestCase
{
    private $client = null;
    private $mentor = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->mentor = $this->client;
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

    private function logInMentor()
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

    public function providerHolidaysDrop()
    {
        $tomorrow = new \DateTime('tomorrow');
        return [
            [250, ["dropped_day" => '2019-05-09'], 404],
            [8, ["dropped_day" => $tomorrow->format('d-m-Y')], 200],
            [11, ["dropped_day" => $tomorrow->format('d-m-Y')], 200],
            [11, ["dropped_day" => '05-01-2018'], 400],
        ];
    }

    /**
     * @param $HolidaysDrop
     * @param $dropped_day
     * @param $expectedCode
     * @dataProvider providerHolidaysDrop
     */
    public function testCalendarDrop($HolidaysDrop, $dropped_day, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/calendar/drop/' . $HolidaysDrop, $dropped_day);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testCalendarDropWithNOtXmlHttpRequest()
    {
        $crawler = $this->client->request('POST', '/admin/calendar/drop/10', ["dropped_day" => '2019-05-20']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerMoveHoliday()
    {
        $tomorrow = new \DateTime('tomorrow');
        return [
            [200, ["moved_day" => '2019-08-09'], 404],
            [9, ["moved_day" => '2010-08-09'], 400],
            [9, ["moved_day" => $tomorrow->format('d-m-Y')], 200],
        ];
    }

    /**
     * @param $moved_day_id
     * @param $moved_day
     * @param $expectedCode
     * @dataProvider providerMoveHoliday
     */
    public function testMoveHoliday($moved_day_id, $moved_day, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/move_holiday/' . $moved_day_id, $moved_day);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testMoveHolidayWithNOtXmlHttpRequest()
    {
        $crawler = $this->client->request('POST', '/admin/move_holiday/10');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerDeleteHoliday()
    {
        return [
            [200, 404],
            [1, 200],
            [9, 200],
        ];
    }

    /**
     * @param $delete_holiday_id
     * @param $expectedCode
     * @dataProvider providerDeleteHoliday
     */
    public function testDelete($delete_holiday_id, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/calendar/delete_holiday/' . $delete_holiday_id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteWithNOtXmlHttpRequest()
    {
        $crawler = $this->client->request('DELETE', '/admin/calendar/delete_holiday/3');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testFindAllHolidays()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/find_all_holidays', ['start' => '01-05-2019', 'cursus_id' => 18]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testFindAllHolidaysWithNOtXmlHttpRequest()
    {
        $crawler = $this->client->request('GET', '/admin/find_all_holidays');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerDeleteFromList()
    {
        return [
            [200, 404],
            [12, 200],
        ];
    }

    /**
     * @param $delete_holiday_id
     * @param $expectedCode
     * @dataProvider providerDeleteFromList
     */
    public function testDeleteFromList($delete_holiday_id, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/calendar/delete_holiday_list/' . $delete_holiday_id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteFromListWithNotXmlHttpRequest()
    {
        $this->client->request('DELETE', '/admin/calendar/delete_holiday_list/11');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerHolidaysAdd()
    {
        $month = date('n');
        $next = $month - 1;
        return [
            [['public_holidays' => ['label' => "test1", 'month' => $next, 'day' => 05]], 201],
            [['public_holidays' => ['label' => "test3", 'month' => 0, 'day' => 0]], 201],
        ];
    }

    /**
     * @param $tab
     * @param $expectedCode
     * @dataProvider providerHolidaysAdd
     */
    public function testHolidayAdd($tab, $expectedCode)
    {
        $this->client->xmlHttpRequest('POST', '/admin/holiday/add', $tab);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testHolidayAddWithNotXmlHttpRequest()
    {
        $this->client->request('POST', '/admin/holiday/add', ['public_holidays' => ['label' => "test2", 'month' => 0, 'day' => 0]]);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/admin/holiday');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testFetchAllYearEvents()
    {
        $crawler = $this->client->request('GET', '/admin/fetch_all_year_events');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testFetchAllYearEventsMentor()
    {
        $this->logInMentor($this->mentor);
        $crawler = $this->mentor->request('GET', '/mentor/fetch_all_year_events');
        $this->assertEquals(200, $this->mentor->getResponse()->getStatusCode());
    }
}
