<?php

namespace App\Tests\ControllerTest;

use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ModuleControllerTest extends WebTestCase
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

    /**
     * @param $url
     * @param $expectedCode
     * @dataProvider providerUrlsShow
     */
    public function testShowModule($url, $expectedCode)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
        if ($this->client->getResponse()->isSuccessful()) {
            $this->assertEquals(1, $crawler->filter('.my-cards')->count());
        }
    }

    public function providerUrlsShow()
    {
        return [
            ['/admin/cursus/1/modules', 200],
//            ['/admin/cursus/500/modules', 404],

        ];
    }

    public function testAddModuleForm()
    {
        $crawler = $this->client->request('POST', '/admin/cursus/1/add_module');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#add-btn')->count());
    }

    public function providerUrlsAdd()
    {
        return [

        [  ['module'=>
              ['title'=>'test module project','description'=>'test project description','duration'=>10], 'moduleType'=>'true'],200,'POST'
        ],

            [  ['module'=>
                ['title'=>'test module normal','description'=>'test description normal'], 'moduleType'=>'false'],200,'POST'
            ],
            [  ['module'=>
                ['title'=>'','description'=>'test description normal'], 'moduleType'=>'false'],400,'POST'
            ],
            [  ['module'=>
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
        $crawler = $this->client->xmlHttpRequest($method, '/admin/cursus/1/add_module',$module);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function providerModuleEdit(){
        return [
         ['GET',61,200,1], ['GET',62,200,1]
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
        $crawler = $this->client->xmlHttpRequest($method, 'admin/cursus/1/module/'.$id.'/edit');
        $this->assertEquals($expctedCode, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($expectedBtn, $crawler->filter('#add-btn')->count());
    }
    public function testEditModuleShowError()
    {
        $crawler = $this->client->request('GET', 'admin/cursus/1/module/61/edit');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('#add-btn')->count());
    }
    public function providerUrlsEdit()
    {
        return [

            [  ['module'=>
                ['title'=>'test module project','description'=>'test project description','duration'=>10],'order'=>1],200,'PUT',

            ],

            [  ['module'=>
                ['title'=>'test module normal','description'=>'test description normal']],200,'PUT'
            ],
            [  ['module'=>
                ['title'=>'module POO','description'=>'test description normal','ordre'=>10]],400,'PUT'
            ],
        ] ;
    }

    /**
     * @param $module
     * @param $expectedCode
     * @param $method
/     * @dataProvider providerUrlsEdit
     */
    public function testEditModuleSubmit($module,$expectedCode,$method)
    {
        $crawler = $this->client->xmlHttpRequest($method, '/admin/cursus/1/module/61/edit',$module);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    public function testEditModuleSubmitError()
    {
        $crawler = $this->client->request('PUT', '/admin/cursus/1/module/61/edit');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public  function providerIdDelete(){
        return[
            [19,200],[500,404]
        ];
    }

    /**
     * @id
     * @expectedCode
     * @dataProvider providerIdDelete
     */

    public function testDeleteModule($id,$expectedCode)
    {
        $this->client->xmlHttpRequest('DELETE', '/admin/cursus/1/module/'.$id.'/delete');
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }
    public function testDeleteModuleError()
    {
        $this->client->request('DELETE', '/admin/cursus/1/module/1/delete');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }




}
