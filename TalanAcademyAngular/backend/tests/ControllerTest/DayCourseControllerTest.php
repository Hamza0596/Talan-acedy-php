<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 24/04/2019
 * Time: 08:54
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class DayCourseControllerTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/admin/day/list/' . $id);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.card-deck-day')->count());
        }
    }

    public function testNew()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/daycourse/add/form/1');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony introduction';
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testNewError()
    {
        $this->client->request('GET','/admin/day/daycourse/add/form/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDayShow()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/1/edit/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formDayEdit')->count());
    }

    public function testEditDaySubmit()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/11');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(12);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitNormalDayNormalDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/16');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(15);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitNormalDayCorrectionDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/11');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(10);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitNormalDayValidatingDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/12');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitNormalDayCorrectionDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/12');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(7);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitNormalDayValidatingDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/12');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(8);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitValidatingDayNormalDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/9');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(9);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidatingDayNormalDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/9');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(12);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidatingDayValidatingDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/7');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(5);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidatingDayValidatingDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/3');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(6);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidatingDayCorrectionDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/5');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(2);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidatingDayCorrectionDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/5');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayNormalDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/4');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(2);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayNormalDaySesondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/7');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(7);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayValidatingDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/2');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayValidatingDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/21');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayCorrectionDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/25');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayCorrectionDaySecondSense()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/25');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(7);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionLastDayValidatingDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/19');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(18);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionDayLastNormalDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/38');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(17);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCorrectionValidating()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/29');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(9);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitLastCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/28');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(18);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitLastCorrectionCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/37');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(8);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidLastNormal()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/12/edit/24');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(19);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidtwo()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/5');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(14);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidValid()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/5');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(13);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitValidCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/9');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(12);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitCheckValidDay()
    {
        $this->client->xmlHttpRequest('POST', '/admin/day/valid_day_check/6/16', ['check' => 'true']);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testEditDaySubmitCorrectionNormal()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/14');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(17);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitCheckValidDayForTest()
    {
        $this->client->xmlHttpRequest('POST', '/admin/day/valid_day_check/6/17', ['check' => 'true']);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testEditDaySubmitValidDayCorrection()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/16');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(17);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testEditDaySubmitDeleteDay()
    {
        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/day/delete/18' );
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'DELETE']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testEditDaySubmitValidDayCorrectionDay()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/6/edit/17');
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['day_course[description]'] = 'symfony TEST';
        $form['order']->select(18);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testEditDaySubmitFail()
    {
        $crawler = $this->client->request('GET', '/admin/day/1/edit/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }


    public function testDayContent()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/day/content/3');
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'DELETE']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formSynopsis')->count());

    }

    public function providerDeleteHoliday()
    {
        return [
            [200, 404],
            [1, 200],
        ];
    }

    /**
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerDeleteHoliday
     */
    public function testFinalPreview($day_id, $expectedCode)
    {
        $crawler = $this->client->request('GET', '/admin/day/final_preview/' . $day_id);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.tabs-container')->count());
        }
    }

    public function synopsys()
    {
        $form = ['day_course_edit' => [
            'synopsis' => "symfony TEST descr"],
        ];;
        return [
            [201, $form,
            ]];
    }

    /**
     * @dataProvider synopsys
     * @param $code
     * @param $form
     */
    public function testSynopsys($code, $form)
    {
        $crawler = $this->client->xmlHttpRequest('POST', '/admin/day/content/3/synopsys', $form);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testSynopsysError()
    {
        $crawler = $this->client->request('POST', '/admin/day/content/3/synopsys');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

    public function testAddOrder()
    {
        $formadd = ['order_course' => [
            'description' => "description",
            'scale' => 1]];
        $this->client->xmlHttpRequest('POST', '/admin/day/addOrder/1', $formadd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }
    public function testAddOrderSame()
    {
        $formadd = ['order_course' => [
            'description' => "description",
            'scale' => 1]];
        $this->client->xmlHttpRequest('POST', '/admin/day/addOrder/1', $formadd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testAddOrderErrorDay()
    {
        $this->client->request('POST', '/admin/day/addOrder/6540');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testAddOrderFormError()
    {
        $formadd = ['activity_courses' => [
            'description' => "description",
            'scale' => 'wfg']];
        $this->client->xmlHttpRequest('POST', '/admin/day/addOrder/1', $formadd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testDeleteOrderErrorRequest()
    {
        $this->client->request('DELETE', '/admin/day/deleteOrder/351247');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteOrderError()
    {
        $this->client->request('DELETE', '/admin/day/deleteOrder/2');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteOrder()
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/day/deleteOrder/1');
        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function providerValidDayCheck()
    {
        return [
            [6, 1, ['check' => 'true'], 200],
            [6, 1, ['check' => 'false'], 200],
            [6, 3, ['check' => 'true'], 200],
            [6, 4, ['check' => 'true'], 200],
            [6, 5, ['check' => 'true'], 200],
            [6, 7, ['check' => 'true'], 200],
            [6, 3, ['check' => 'true'], 200],
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
        $this->client->xmlHttpRequest('POST', '/admin/day/valid_day_check/' . $module_id . '/' . $day_id, $check_value);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testValidDayCheckWithoutRequest()
    {
        $this->client->request('POST', '/admin/day/valid_day_check/6/10', ['check' => 'true']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerDeleteDay()
    {
        return [
            [2, 200],
        ];
    }

    /**
     * @param $day_id
     * @param $expectedCode
     * @dataProvider providerDeleteDay
     */
    public function testDeleteDay($day_id, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('DELETE', '/admin/day/delete/' . $day_id);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'DELETE']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testLoadResources()
    {
        $this->client->request('POST', '/admin/day/loadRessources/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testApproveResources()
    {
        $this->client->xmlHttpRequest('POST', '/admin/day/approve/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testDisapproveResources()
    {
        $this->client->xmlHttpRequest('GET', '/admin/day/disapprove/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
    public function testApproveResourcesFail()
    {
        $this->client->request('GET', '/admin/day/approve/2');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testDisapproveResourcesFail()
    {
        $this->client->request('GET', '/admin/day/disapprove/2');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditOrder()
    {
        $this->client->xmlHttpRequest('POST','/admin/day/editOrder/2',['description'=>'description modified','scale'=>1]);
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());

    }
    public function testEditOrderError()
    {
        $this->client->request('POST','/admin/day/editOrder/2');
        $this->assertEquals(400,$this->client->getResponse()->getStatusCode());

    }
//    public function testDeleteOrders(){
//        $this->client->xmlHttpRequest('POST','/admin/day/deleteOrders/2');
//        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
//
//    }
//    public function testDeleteNoOrders(){
//        $this->client->xmlHttpRequest('POST','/admin/day/deleteOrders/2');
//        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
//
//    }
//
//    public function testDeleteOrdersError()
//    {
//        $this->client->request('POST','/admin/day/deleteOrders/5');
//        $this->assertEquals(400,$this->client->getResponse()->getStatusCode());
//
//    }

}
