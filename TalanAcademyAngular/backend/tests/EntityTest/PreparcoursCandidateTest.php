<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 13/11/2019
 * Time: 09:58
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class PreparcoursCandidateTest extends TestCase
{

    public function testPreparcoursCandidateCreate()
    {
        $preparcoursCandidate = new PreparcoursCandidate();
        $preparcoursCandidate->setDescription('preparcours description');
        $preparcoursCandidate->setStatus(PreparcoursCandidate::EN_COURS);
        $preparcoursCandidate->setStartDate(new \DateTime('2019-04-25'));
        $preparcoursCandidate->setPreparcoursPdf('monCv.pdf');
        $preparcoursCandidate->setRepoGit('candidate');
        $preparcoursCandidate->setDecision('decision test');
        $preparcoursCandidate->setSubmissionDate(new \DateTime('2019-04-25'));
        $preparcours = new Preparcours();
        $preparcoursCandidate->setPreparcours($preparcours);
        $preparcoursCandidate->getPreparcours()->setDescription('preparcours test');
        $student = new Student();
        $preparcoursCandidate->setCandidate($student);
        $preparcoursCandidate->getCandidate()->setFirstName('student name');
        $candidature = new Candidature();
        $preparcoursCandidate->setCandidature($candidature);
        $preparcoursCandidate->getCandidature()->setStatus(Candidature::NOUVEAU);


        $this->assertEquals('preparcours description', $preparcoursCandidate->getDescription());
        $this->assertEquals(PreparcoursCandidate::EN_COURS, $preparcoursCandidate->getStatus());
        $this->assertEquals(new \DateTime('2019-04-25'), $preparcoursCandidate->getStartDate());
        $this->assertEquals(new \DateTime('2019-04-25'), $preparcoursCandidate->getSubmissionDate());
        $this->assertEquals('monCv.pdf', $preparcoursCandidate->getPreparcoursPdf());
        $this->assertEquals('candidate', $preparcoursCandidate->getRepoGit());
        $this->assertEquals('decision test', $preparcoursCandidate->getDecision());
        $this->assertEquals(Candidature::NOUVEAU, $preparcoursCandidate->getCandidature()->getStatus());
        $this->assertEquals('student name', $preparcoursCandidate->getCandidate()->getFirstName());
        $this->assertEquals('preparcours test', $preparcoursCandidate->getPreparcours()->getDescription());


    }

}
