<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:36
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{

    public function testStudentCreate()
    {
        $student = new Student();
        $student->setCity('Tunis');
        $student->setBirthday(new \DateTime('17-10-1987'))
            ->setTel('21212212');
        $candidature1 = new Candidature();
        $candidature2 = new Candidature();
        $student->addCandidature($candidature1);
        $student->addCandidature($candidature2);
        $student->removeCandidature($candidature1);
        $affectation1 = new Affectation();
        $affectation2 = new Affectation();
        $student->addAffectation($affectation1);
        $student->addAffectation($affectation2);
        $student->removeAffectation($affectation2);
        $student->setStatus(Student::APPRENTI);
        $this->assertEquals('Tunis', $student->getCity());
        $this->assertEquals(new \DateTime('17-10-1987'), $student->getBirthday());
        $this->assertEquals('21212212', $student->getTel());
        $this->assertEquals(1, $student->getCandidatures()->count());
        $studentReviews = new StudentReview();
        $student->addStudentReview($studentReviews);
        $this->assertEquals($studentReviews, $student->getStudentReviews()[0]);
        $this->assertEquals(Student::APPRENTI, $student->getStatus());
        $student->removeStudentReview($studentReviews);
        $student->getGradesValidation();
        $submissionWorks = new SubmissionWorks();
        $student->addSubmissionWork($submissionWorks);
        $this->assertEquals($submissionWorks, $student->getSubmissionWorks()[0]);
        $this->assertEquals(1, $student->getAffectations()->count());
        $student->removeSubmissionWork($submissionWorks);
        $sessionResourceRecommendation1 = new ResourceRecommendation();
        $sessionResourceRecommendation2 = new ResourceRecommendation();
        $student->addResourceRecommendation($sessionResourceRecommendation1);
        $student->addResourceRecommendation($sessionResourceRecommendation2);
        $student->removeResourceRecommendation($sessionResourceRecommendation2);
        $this->assertEquals(1,count($student->getResourceRecommendation()));


    }
}
