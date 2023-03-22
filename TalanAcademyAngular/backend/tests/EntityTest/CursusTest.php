<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:34
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class CursusTest extends TestCase
{

    public function testCursusCreate()
    {
        $cursus = new Cursus();
        $cursus->setName('PHP')
               ->setDescription('La première semaine nous allons voir les bases de l\'informatique ')
               ->setVisibility($cursus::VISIBLE)
               ->setImage('https://symfony.png')
            ->setTags('php,poo');
        $module1 = new Module();
        $module2 = new Module();
        $cursus->addModule($module1);
        $cursus->addModule($module2);
        $cursus->getModules()[0]->setTitle('Gestion de fichiers');
        $cursus->removeModule($module2);
        $staff1 = new Staff();
        $staff2 = new Staff();
        $cursus->addStaff($staff1);
        $cursus->addStaff($staff2);
        $cursus->getStaff()[0]->setFirstName('john doe');
        $cursus->removeStaff($staff2);

        $candidature1 =new Candidature();
        $candidature2 =new Candidature();
        $cursus->addCandidature($candidature1);
        $cursus->addCandidature($candidature2);
        $cursus->removeCandidature($candidature1);
        $session1 =new Session();
        $session2 =new Session();
        $cursus->addSession($session1);
        $cursus->addSession($session2);
        $cursus->removeSession($session2);
        $cursus->getSessions()[0]->setDaysNumber(30);

        $cursus->setDaysNumber(60);



        $this->assertEquals(null, $cursus->getId());
        $this->assertEquals(60, $cursus->getDaysNumber());
        $this->assertEquals('PHP',$cursus->getName());
        $this->assertEquals('La première semaine nous allons voir les bases de l\'informatique ',$cursus->getDescription());
        $this->assertEquals($cursus::VISIBLE,$cursus->getVisibility());
        $this->assertEquals('https://symfony.png',$cursus->getImage());
        $this->assertEquals('php,poo',$cursus->getTags());
        $this->assertEquals(['php','poo'],$cursus->getTagsArray());
        $this->assertEquals(1,$cursus->getModules()->count());
        $this->assertEquals(1,$cursus->getCandidatures()->count());
        $this->assertInstanceOf(Staff::class,$cursus->getStaff()[0]);
        $this->assertEquals('john doe',$cursus->getStaff()[0]->getFirstName());
        $this->assertEquals(1,$cursus->getStaff()->count());
        $this->assertInstanceOf(Session::class,$cursus->getSessions()[0]);
        $this->assertEquals(30,$cursus->getSessions()[0]->getDaysNumber());
        $this->assertEquals(1,$cursus->getSessions()->count());



    }
}
