<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 19:15
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class CandidatureTest extends TestCase
{
    public function testCandidatureCreate()
    {
        $candidature = new Candidature();
        $candidature->setStatus(Candidature::ACCEPTE);
        $candidature->setDegree('Ingénieur Info');
        $candidature->setGrades('Bac+5 (Ingénieur, Master2)');
        $candidature->setCv('monCv.pdf');
        $candidature->setItExperience(true);
        $candidature->setLinkLinkedin('https://fr.linkedin.com/in/22');
        $candidature->setCurrentSituation('Salarié');
        $candidature->setDatePostule(new \DateTime('2019-04-23'));
        $cursus = new Cursus();
        $candidature->setCursus($cursus);
        $student = new Student();
        $candidature->setCandidat($student);
        $candidature->getId();
        $candidatureState = new CandidatureState();
        $candidature->addCandidatureState($candidatureState);
        $preparcoursCandidate = new PreparcoursCandidate();
        $candidature->setPreparcoursCandidate($preparcoursCandidate);
        $candidature->getPreparcoursCandidate()->setDescription('preparcours test');
        $this->assertEquals(Candidature::ACCEPTE, $candidature->getStatus());
        $this->assertEquals('Ingénieur Info', $candidature->getDegree());
        $this->assertEquals('Bac+5 (Ingénieur, Master2)', $candidature->getGrades());
        $this->assertEquals('monCv.pdf', $candidature->getCv());
        $this->assertEquals(true, $candidature->getItExperience());
        $this->assertEquals('https://fr.linkedin.com/in/22', $candidature->getLinkLinkedin());
        $this->assertEquals('Salarié', $candidature->getCurrentSituation());
        $this->assertEquals($candidatureState, $candidature->getCandidatureStates()[0]);
        $this->assertEquals(new \DateTime('2019-04-23'), $candidature->getDatePostule());
        $this->assertInstanceOf(Cursus::class, $candidature->getCursus());
        $this->assertInstanceOf(Student::class, $candidature->getCandidat());
        $candidature->removeCandidatureState($candidatureState);
        $sessionUser = new SessionUserData();
        $candidature->setSessionUserData($sessionUser);
        $this->assertEquals($sessionUser, $candidature->getSessionUserData());

    }
}
