<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 15/05/2019
 * Time: 14:38
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SessionModuleTest extends KernelTestCase
{
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new SessionModule());
    }

    public function testModule()
    {
        $module = new SessionModule();
        $module->setTitle('Découverte de PHP');
        $module->setDescription('Installation & découverte de PHP, historique du langague et notions de base');
        $module->setRef('ref_activity521124587');
        $module->setSession(new Session());
        $day1 = new SessionDayCourse();
        $day2 = new SessionDayCourse();
        $module->addDayCourse($day1);
        $module->addDayCourse($day2);
        $module->getDayCourses()[0]->setStatus(SessionDayCourse::NORMAL_DAY);
        $module->removeDayCourse($day2);
        $module->setOrderModule(1);
        $sessionProjectSubject1 = new SessionProjectSubject();
        $sessionProjectSubject2 = new SessionProjectSubject();
        $module->addSessionProjectSubject($sessionProjectSubject1);
        $module->addSessionProjectSubject($sessionProjectSubject2);
        $module->removeSessionProjectSubject($sessionProjectSubject1);

        $this->assertEquals('Découverte de PHP', $module->getTitle());
        $this->assertEquals('Installation & découverte de PHP, historique du langague et notions de base', $module->getDescription());
        $this->assertEquals('ref_activity521124587', $module->getRef());
        $this->assertEquals(null, $module->getId());
        $this->assertEquals(1, $module->getOrderModule());
        $this->assertInstanceOf(Session::class, $module->getSession());
        $this->assertInstanceOf(SessionModule::class, $module);
        $this->assertEquals('jour-normal', $module->getDayCourses()[0]->getStatus());
        $this->assertEquals(1, $module->getDayCourses()->count());
        $this->assertEquals(1, $module->getSessionProjectSubjects()->count());
        $this->assertEquals(4,count($module->serializer()));

    }
    public function testCustomValidator()
    {
        $this->assertEquals(3, $this->validator->count());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[0]->getMessage());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[1]->getMessage());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[2]->getMessage());
    }

}
