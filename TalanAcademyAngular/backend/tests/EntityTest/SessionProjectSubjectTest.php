<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 14/11/2019
 * Time: 16:04
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class SessionProjectSubjectTest extends TestCase
{

    public function testCreate(){
        $copy= ['name'=>'subject name','ref'=>'subject ref','specification'=>'specification  subject'];
        $sessionProjectSubjectCopy = new SessionProjectSubject($copy);
        $sessionProjectSubject = new SessionProjectSubject();
        $sessionProjectSubject->setName('subject1');
        $sessionProjectSubject->setStatus(SessionProjectSubject::ACTIVATED);
        $sessionProjectSubject->setRef('t09980630');
        $sessionProjectSubject->setSpecification('specification test');
        $sessionProject = new SessionModule();
        $sessionProject->setDescription('project test');
        $sessionProjectSubject->setSessionProject($sessionProject);
        $affectation1 =new Affectation();
        $affectation2 =new Affectation();
        $sessionProjectSubject->addAffectation($affectation1);
        $sessionProjectSubject->addAffectation($affectation2);
        $sessionProjectSubject->removeAffectation($affectation1);
        $subjectDayContent1 = new SubjectDayContent();
        $subjectDayContent2 = new SubjectDayContent();
        $sessionProjectSubject->addSubjectDayContent($subjectDayContent1);
        $sessionProjectSubject->addSubjectDayContent($subjectDayContent2);
        $sessionProjectSubject->removeSubjectDayContent($subjectDayContent1);
        $mentor1 = new Staff();
        $mentor2 = new Staff();
        $sessionProjectSubject->addMentor($mentor1);
        $sessionProjectSubject->addMentor($mentor2);
        $sessionProjectSubject->removeMentor($mentor1);

        $this->assertEquals(null, $sessionProjectSubject->getId());
        $this->assertEquals('subject1', $sessionProjectSubject->getName());
        $this->assertEquals(SessionProjectSubject::ACTIVATED, $sessionProjectSubject->getStatus());
        $this->assertEquals('t09980630', $sessionProjectSubject->getRef());
        $this->assertEquals('specification test', $sessionProjectSubject->getSpecification());
        $this->assertEquals('project test', $sessionProjectSubject->getSessionProject()->getDescription());
        $this->assertEquals(1, $sessionProjectSubject->getAffectations()->count());
        $this->assertEquals(1, $sessionProjectSubject->getSubjectDayContents()->count());
        $this->assertEquals(1, $sessionProjectSubject->getMentor()->count());
        $this->assertEquals([
            'name' => $sessionProjectSubject->getName(),
            'specification' => $sessionProjectSubject->getSpecification(),
            'ref' => $sessionProjectSubject->getRef(),
        ], $sessionProjectSubject->serializer());




    }

}
