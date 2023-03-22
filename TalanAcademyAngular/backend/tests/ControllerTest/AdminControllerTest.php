<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 15/05/2019
 * Time: 13:42
 */

namespace App\Tests\ControllerTest;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class AdminControllerTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
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

    public function testShowStaff()
    {
        $crawler = $this->client->request('GET', '/admin/staffDataTable');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#DataTables_Table_0')->count());
    }

    public function testGetAddModal()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/getModalAdd');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formStaff')->count());
        $this->client->request('GET', '/admin/getModalAdd');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $firstName
     * @param $email
     * @param $expectedCode
     * @dataProvider staffProvider
     */
    public function testAddStaff($firstName, $email, $function, $expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/getModalAdd');
        $this->assertEquals(1, $crawler->filter('#add-btn')->count());
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['staff_admin[firstName]'] = $firstName;
        $form['staff_admin[lastName]'] = 'lastname';
        $form['staff_admin[email]'] = $email;
        $form['staff_admin[function]']->select($function);
        $form['staff_admin[cursus]']->select(1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->client->submit($form);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());

    }

    public function testAddStaffFailed()
    {
        $this->client->request('GET', '/admin/addStaff');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function staffProvider()
    {
        return [
            ['', '', 'administrateur', 400],
            ['firstname', 'new.email@gmail.com', 'administrateur', 200],
            ['firstname', 'new.email2@gmail.com', 'mentor', 200],
            ['firstName', 'admin@talan.com', 'administrateur', 400],
        ];
    }

    public function testFormAddPassword()
    {
        $token = 'a1234';
        $crawler = $this->client->request('GET', '/admin/staff_password/' . $token);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formPasswordStaff')->count());
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['password_staff[password][first]'] = 'test12345';
        $form['password_staff[password][second]'] = 'test12345';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200,
            $this->client->getResponse()->getStatusCode());
    }

    public function testGetEditStaff()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/getEditStaff/8');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#formStaffEdit')->count());
        $this->client->request('GET', '/admin/getEditStaff/8');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param $firstName
     * @param $function
     * @dataProvider editStaffProvider
     */
    public function testEditStaff($firstName, $function)
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/getEditStaff/8');
        $this->assertEquals(1, $crawler->filter('#formStaffEdit')->count());

        $form = $crawler->selectButton('ENREGISTRER')->form();
        $form['staff_admin[firstName]'] = $firstName;
        $form['staff_admin[function]']->select($function);
        $form['staff_admin[cursus]']->select(1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'PUT']);
        $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function editStaffProvider()
    {

        return [
            ['firstname', 'administrateur'],
            ['firstname', 'mentor'],
        ];
    }

//

    public function testEditStaffFailed()
    {
        $crawler = $this->client->xmlHttpRequest('GET', '/admin/getEditStaff/8');
        $this->assertEquals(1, $crawler->filter('#formStaffEdit')->count());
        $form = $crawler->selectButton('ENREGISTRER')->form();
        $this->client->submit($form);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function providerResetPwd()
    {
        return [
            [['password_staff' =>['password'=> ['first' => "admin1234k5",
                'second' => 'admin12345']]

            ],200],
              [['password_staff' =>['password'=> ['first' => "admin12345",
                  'second' => 'admin12345']]

                ],302]

        ];
    }

    /**
     * @param $pwd
     * @param $expectedCode
     * @dataProvider providerResetPwd
     */
    public function testResetPassword($pwd,$expectedCode)
    {
        $crawler = $this->client->xmlHttpRequest('POST','admin/reset_password/a123456',$pwd);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }



    public function testResetPasswordFail()
    {
        $this->client->request('GET', '/admin/reset_password/test');
        $this->client->followRedirects();
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testResetPasswordStaffMail()
    {
        $crawler = $this->client->request('GET', '/admin/mail_reset_password/8');
        $this->assertEquals('success', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDisableEnableStaff()
    {
        $this->client->xmlHttpRequest('GET', '/admin/disableEnableStaff/8');
        $this->assertEquals('success', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testDisableEnableStaffd()
    {
        $this->client->xmlHttpRequest('GET', '/admin/disableEnableStaff/9');
        $this->assertEquals('success', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDisableEnableStaffFailed()
    {
        $this->client->request('POST', '/admin/disableEnableStaff/9');
        $this->assertEquals('fail', $this->client->getResponse()->getContent());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTable()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $columns = [['data' => '0', 'name' => 'lastName', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'firstName', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'email', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'function', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'cursus', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/staff', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDashboard()
    {
        $this->client->request('GET', '/admin/dashboard');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testStaticDashboard()
    {
        $this->client->request('GET', '/admin/staticDashboard');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteStaff()
    {
        $this->client->xmlHttpRequest('GET', '/admin/deleteStaff/8');
        $this->assertEquals('success', $this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDataTableSessionList()
    {
        $draw = '1';
        $start = '0';
        $length = '10';
        $search = ['value' => '', 'regex' => 'false'];
        $order = [['column' => '2', 'dir' => 'asc']];
        $extra_search = '[]';
        $columns = [
            ['data' => '0', 'name' => 'Session', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '1', 'name' => 'Date de début', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '2', 'name' => 'Apprentis', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '3', 'name' => 'Moyenne', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '4', 'name' => 'Evaluation', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '5', 'name' => 'Avancement', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ['data' => '6', 'name' => 'actions', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
        ];
        $this->client->xmlHttpRequest('POST', '/admin/admin_Session_list', ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search, 'columns' => $columns, 'order' => $order, 'extra_search'=>$extra_search]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmitCorrection()
    {
        $this->client->xmlHttpRequest('POST','/admin/submitCorrection/2',['resultTrue'=>[3],'correctedId'=>1]);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

    } public function testSubmitCorrectionFalse()
    {
        $this->client->xmlHttpRequest('POST','/admin/submitCorrection/2',['resultTrue'=>[4],'correctedId'=>18]);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

    }
    public function testSubmitCorrectionError()
    {
        $this->client->request('POST','/admin/submitCorrection/2',['resultTrue'=>[3],'correctedId'=>1]);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }
    public function testStaticDashAdmin()
    {
        $this->client->request('POST','/admin/staticDashAdmin');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testExportActivitySessionPdf()
    {
        $this->client->request('POST','/admin/download_activities/session/2');
        $this->assertEquals( 'text/html; charset=UTF-8',$this->client->getResponse()->headers->get('content-type'));
    }
    public function testExportActivityCursusPdf()
    {
        $this->client->request('POST','/admin/download_activities/cursus/2');
        $this->assertEquals( 'text/html; charset=UTF-8',$this->client->getResponse()->headers->get('content-type'));
    }

    public function testGetExcelExampleConsignes()
    {
        $this->client->request('POST','/admin/download_example_questions');
        $this->assertEquals('inline; filename=modele_consignes.xlsx',$this->client->getResponse()->headers->get('content-disposition'));
    }
    public function testGetExcelExampleActivity()
    {
        $this->client->request('POST','/admin/download_example_activity');
        $this->assertEquals('inline; filename=modele_activite.xlsx',$this->client->getResponse()->headers->get('content-disposition'));
    }

    public function testUpdateApprenticeStatus()
    {
        $this->client->xmlHttpRequest('POST', '/admin/updateApprenticeStatus/14');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testUpdateApprenticeStatusError()
    {
        $this->client->request('POST', '/admin/updateApprenticeStatus/14');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

    }

}
