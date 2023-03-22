<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 13/11/2019
 * Time: 10:19
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class AffectationTest extends TestCase
{
    public function testAffectationCreate()
    {
        $affectation = new Affectation();
        $sessionProjectSubject = new SessionProjectSubject();
        $affectation->setSubject($sessionProjectSubject);
        $affectation->getSubject()->setStatus(SessionProjectSubject::ACTIVATED);
        $student = new Student();
        $affectation->setStudent($student);
        $affectation->getStudent()->setFirstName('name');
        $this->assertEquals(null,$affectation->getId());
        $this->assertEquals(SessionProjectSubject::ACTIVATED, $affectation->getSubject()->getStatus());
        $this->assertEquals('name', $affectation->getStudent()->getFirstName());


    }

}
