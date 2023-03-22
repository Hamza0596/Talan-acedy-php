<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 12/11/2019
 * Time: 14:15
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PreparcoursTest extends KernelTestCase
{
    public function testPreparcoursCreate()
    {
        $preparcours = new Preparcours();
       $preparcours->setDescription('preparcours description');
       $preparcours->setIsActivated(1);
       $preparcours->setDateCreation('12-11-2019');
       $preparcours->setPdf('preparcours');
        $preparcoursCandidate = new PreparcoursCandidate();
        $preparcours->addPreparcoursCandidate($preparcoursCandidate);
        $preparcoursCandidate1 = new PreparcoursCandidate();
        $preparcours->addPreparcoursCandidate($preparcoursCandidate1);
        $preparcours->getPreparcoursCandidate()[0]->setDescription('description');
        $preparcours->removePreparcoursCandidate($preparcoursCandidate);

        $this->assertEquals(null, $preparcours->getId());
        $this->assertEquals('preparcours description', $preparcours->getDescription());
        $this->assertEquals(1,$preparcours->getIsActivated());
        $this->assertEquals('12-11-2019',$preparcours->getDateCreation());
        $this->assertEquals('preparcours',$preparcours->getPdf());
        $this->assertEquals(1,$preparcours->getPreparcoursCandidate()->count());

    }
}
