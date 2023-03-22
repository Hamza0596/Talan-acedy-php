<?php

namespace App\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ModuleTest extends KernelTestCase
{
    private $validator;

    public function testModule()
    {
        $module = new Module();
        $module->setTitle('Découverte de PHP');
        $module->setDescription('Installation & découverte de PHP, historique du langague et notions de base');
        $module->setRef('ref_activity521124587');
        $module->setCourses(new Cursus());
        $module->setDeleted(1);
        $day1 = new DayCourse();
        $day2 = new DayCourse();
        $module->addDayCourse($day1);
        $module->addDayCourse($day2);
        $module->getDayCourses()[0]->setStatus(DayCourse::NORMAL_DAY);
        $module->removeDayCourse($day2);
        $module->setOrderModule(1);
        $projectSubject1 = new ProjectSubject();
        $projectSubject2 = new ProjectSubject();
        $module->addProjectSubject($projectSubject1);
        $module->addProjectSubject($projectSubject2);
        $module->getProjectSubjects()[0]->setName('subject test');
        $module->removeProjectSubject($projectSubject2);



        $this->assertEquals('Découverte de PHP', $module->getTitle());
        $this->assertEquals('Installation & découverte de PHP, historique du langague et notions de base', $module->getDescription());
        $this->assertEquals('ref_activity521124587', $module->getRef());
        $this->assertEquals(null, $module->getId());
        $this->assertEquals(1, $module->getOrderModule());
        $this->assertEquals(1, $module->getDeleted());
        $this->assertInstanceOf(Cursus::class, $module->getCourses());
        $this->assertEquals('jour-normal', $module->getDayCourses()[0]->getStatus());
        $this->assertEquals(1, $module->getDayCourses()->count());
        $this->assertEquals(1, $module->getProjectSubjects()->count());

    }

    public function testCustomValidator()
    {
        $this->assertEquals(3, $this->validator->count());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[0]->getMessage());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[1]->getMessage());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[2]->getMessage());
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new Module());
    }
}
