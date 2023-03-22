<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 14/05/2019
 * Time: 14:14
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class DayCourseSessionControllerTest extends WebTestCase
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

    public function providerUrlsShow()
    {
        return [
            [1, 200],
            [510, 404],
        ];
    }

    /**
     * @dataProvider providerUrlsShow
     * @param $id
     * @param $expectedCode
     */
    public function testDayList($id, $expectedCode)
    {
        $crawler = $this->client->request('GET', '/admin/session/day/list/' . $id);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.card-deck-day')->count());
        }
    }
    public function providerUrlsShowMentor()
    {
        return [
            [1, 200],
            [510, 404],
            [6, 302],
        ];
    }


    /**
     * @dataProvider providerUrlsShowMentor
     * @param $id
     * @param $expectedCode
     */
    public function testDayListMentor($id, $expectedCode)
    {
        $this->logInMentor();
        $crawler = $this->mentor->request('GET', '/mentor/session/day/list/' . $id);
        $this->assertEquals($expectedCode, $this->mentor->getResponse()->getStatusCode());
        if ($this->mentor->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.card-deck-day')->count());
        }
    }

    public function testGetAddForm()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/daycourse/add/form/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectButton('ENREGISTRER')->count());

    }

    public function testGetAddFormError()
    {
        $crawler = $this->client->request('GET', '/admin/session/day/daycourse/add/form/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testNew()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/daycourse/add/form/1');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_day_course[description]'] = 'symfony introduction';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testNewError()
    {
        $crawler = $this->client->request('POST', '/admin/session/day/new/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditDayShow()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/3/edit/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formDayEdit')->count());
    }

    public function testEditDaySubmit()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/32/edit/16');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_day_course[description]'] = 'symfony TEST descr';
        $form['order']->select(1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitModuleInProgress()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/43/edit/70');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_day_course[description]'] = 'symfony TEST descr';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitFail()
    {
        $crawler = $this->client->request('GET', '/admin/day/1/edit/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitError()
    {
        $crawler = $this->client->request('GET', '/admin/session/day/3/edit/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitOldOrderLessThanNewOrder()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/32/edit/16');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['session_day_course[description]'] = 'symfony TEST descr';
        $form['order']->select(2);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }


    public function testDayContent()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/content/5');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formSynopsis')->count());

    }

    public function testDayContentNotFound()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/content');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

    }

    public function testFinalPreview()
    {
        $crawler = $this->client->request('GET', '/admin/session/day/final_preview/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#Ressources')->count());

    }

    public function synopsys()
    {
        $form = ['day_session_edit' => [
            'synopsis' => "symfony TEST descr"],
        ];;
        return [
            [200, $form,
            ]];
    }

    /**
     * @dataProvider synopsys
     * @param $expectedCode
     * @param $form
     */
    public function testSynopsys($expectedCode,$form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/session/day/content/3/synopsys', $form);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());

    }

    public function testSynopsysError()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/session/day/content/3/synopsys');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }



    public function order()
    {
        $form = ['session_order' => [
            'description' => "symfony TEST descr 54514",
            'scale' => 1],
        ];;
        return [
            [201, $form,
            ]];
    }

    /**
     * @dataProvider order
     * @param $code
     * @param $form
     */
    public function testAddOrder($code, $form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/session/day/addOrder/3', $form);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());

    }

    public function orderError()
    {
        $form = ['session' => [
            'description' => "symfony TEST descr 54514",
            'scale' => 1],
        ];;
        return [
            [400, $form,
            ]];
    }

    /**
     * @dataProvider orderError
     * @param $code
     * @param $form
     */
    public function testAddOrderError($code, $form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/session/day/addOrder/3', $form);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($code, $this->client->getResponse()->getStatusCode());

    }

    public function testDeleteOrder()
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/session/day/deleteOrder/2');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteOrderError()
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/session/day/deleteOrder/120');
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'DELETE']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteOrderError1()
    {
        $this->client->request('DELETE', '/admin/session/day/deleteOrder/3');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerValidDayCheck()
    {
        return [
            [1, 1, ['check' => 'false'], 200],
            [1, 1, ['check' => 'false'], 200],
            [1, 1, ['check' => 'true'], 200],
            [1, 3, ['check' => 'true'], 200],
            [1, 4, ['check' => 'true'], 200],
            [1, 5, ['check' => 'true'], 200],
            [1, 7, ['check' => 'true'], 200],
            [1, 18, ['check' => 'true'], 200],
        ];
    }

    /**
     * @param $module_id
     * @param $day_id
     * @param $check_value
     * @param $expectedCode
     * @dataProvider providerValidDayCheck
     */
    public function testValidDayCheck($module_id, $day_id, $check_value, $expectedCode)
    {
        $this->client->xmlHttpRequest('POST', '/admin/session/day/session_valid_day_check/' . $module_id . '/' . $day_id, $check_value);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testValidDayCheckWithoutRequest()
    {
        $this->client->request('POST', '/admin/session/day/session_valid_day_check/2/15', ['check' => 'true']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteDayError()
    {
        $this->client->request('DELETE', '/admin/session/day/delete/47');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerDeleteDay()
    {
        return [
            [47, 200],
        ];
    }


    /**
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerDeleteDay
     */
    public function testDeleteDay($day_id, $expectedCode)
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/session/day/delete/' . $day_id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'DELETE']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }
    public function testEditOrder()
    {
        $this->client->xmlHttpRequest('POST','/admin/session/day/editOrder/3',['description'=>'description modified','scale'=>1]);
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());

    }
    public function testEditOrderError()
    {
        $this->client->request('POST','/admin/session/day/editOrder/3');
        $this->assertEquals(400,$this->client->getResponse()->getStatusCode());

    }


}
