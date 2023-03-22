<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 15/05/2019
 * Time: 16:27
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ModuleSessionControllerTest extends WebTestCase
{
    private $client = null;
    private $mentor = null;
    private $mentor1 = null;


    public function setUp()
    {
        $this->client = static::createClient();
        $this->mentor = $this->client;
        $this->mentor1 = $this->client;
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
    private function logInMentorWithoutSession()
    {
        $session = $this->mentor1->getContainer()->get('session');
        $user = $this->mentor1->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('mentorWithoutSession@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->mentor1->getCookieJar()->set($cookie);
    }

    /**
     * @param $url
     * @param $expectedCode
     * @dataProvider providerUrlsShow
     */
    public function testShowModule($url, $expectedCode)
    {
        $this->client->setServerParameter('HTTP_REFERER','/admin/dashboard');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.my-cards')->count());
        }
    }

    public function testShowModuleMentor()
    {
        $this->logInMentor();
        $this->mentor->request('GET', '/mentor/session/1');
        $this->assertEquals(200, $this->mentor->getResponse()->getStatusCode());
    }
    /**
     * @param $url
     * @param $expectedCode
     * @dataProvider providerUrlsShow
     */
    public function testShowModuleUrl2($url, $expectedCode)
    {
        $this->client->setServerParameter('HTTP_REFERER','/admin/cursus/');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.my-cards')->count());
        }
    }

    public function testShowModuleMentorWithoutSession()
    {
        $this->logInMentorWithoutSession();
        $this->mentor1->request('GET', '/mentor/session/1');
        $this->assertEquals(302,$this->mentor1->getResponse()->getStatusCode());


    }

    public function providerUrlsShow()
    {
        return [
            ['/admin/session/1', 200],

        ];
    }




    public function testAddModuleForm()
    {
        $crawler = $this->client->request('POST', '/admin/session/1/add_module');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#add-btn')->count());
    }

    public function providerUrlsAdd()
    {
        return [
            [  ['session_module'=>
                ['title'=>'test module project','description'=>'test project description','duration'=>10], 'moduleType'=>'true'],200,'POST'
            ],

            [  ['session_module'=>
                ['title'=>'test module normal','description'=>'test description normal'], 'moduleType'=>'false'],200,'POST'
            ],
            [  ['session_module'=>
                ['title'=>'','description'=>'test description normal'], 'moduleType'=>'false'],400,'POST'
            ],
            [  ['session_module'=>
                ['title'=>'','description'=>'test description normal'], 'moduleType'=>'false'],200,'GET'
            ]
        ] ;
    }

    /**
     * @param $module
     * @param $expectedCode
     * @param $method
     * @dataProvider providerUrlsAdd
     */
    public function testAddModuleSubmitForm($module,$expectedCode,$method)
    {
        $crawler = $this->client->xmlHttpRequest($method, '/admin/session/1/add_module',$module);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }


    public function testEditModuleShowError()
    {
        $crawler = $this->client->request('GET', 'admin/session/1/module/1/edit');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

//    public function testEditModuleShow1()
//    {
//        $crawler = $this->client->xmlHttpRequest('GET', 'admin/session/1/module/1/edit');
//        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
//        $this->assertEquals(1, $crawler->filter('#add-btn')->count());
//    }
    public function providerModuleEdit(){
        return [
            ['GET',31,200,1], ['GET',32,200,1]
        ];
    }

    /**
     * @param $method
     * @param $id
     * @param $expctedCode
     * @param $expectedBtn
     * @dataProvider  providerModuleEdit
     */
    public function testEditModuleShow($method,$id,$expctedCode,$expectedBtn)
    {

        $crawler = $this->client->xmlHttpRequest($method, 'admin/session/1/module/'.$id.'/edit');
        $this->assertEquals($expctedCode, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($expectedBtn, $crawler->filter('#add-btn')->count());
    }

    public function providerUrlsEdit()
    {
        return [

            [  ['session_module'=>
                ['title'=>'test module project','description'=>'test project description','duration'=>10]],200,'PUT',8,32

            ],
            [  ['session_module'=>
                ['title'=>'test module project','description'=>'test project description'],'order'=>1],400,'PUT',1,2

            ],
            [  ['session_module'=>
                ['title'=>'test module project','description'=>'test project description'],'order'=>2],200,'PUT',1,1

            ],

            [  ['session_module'=>
                ['title'=>'test module normal','description'=>'test description normal']],200,'PUT',1,1
            ],

        ] ;
    }

    /**
     * @param $module
     * @param $expectedCode
     * @param $method
     * @param $idSession
     * @param $idModule
     * @dataProvider providerUrlsEdit
     */
    public function testEditModuleSubmit($module,$expectedCode,$method,$idSession,$idModule)
    {
        $crawler = $this->client->xmlHttpRequest($method, '/admin/session/'.$idSession.'/module/'.$idModule.'/edit',$module);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testEditModuleSubmitError()
    {
        $crawler = $this->client->request('PUT', '/admin/session/8/module/32/edit');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testDeleteModule()
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/session/1/module/2/delete');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteModule1()
    {
        $this->client->request('DELETE', '/admin/session/1/module/18/delete');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


}
