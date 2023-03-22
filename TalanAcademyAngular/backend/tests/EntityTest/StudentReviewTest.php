<?php

namespace App\Tests\EntityTest;

use App\Entity\SessionDayCourse;
use App\Entity\Student;
use App\Entity\StudentReview;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StudentReviewTest extends KernelTestCase
{

    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new StudentReview());
    }

    public function testModel()
    {
        $StudentReview = new StudentReview();
        $this->assertEquals(null, $StudentReview->getId());
        $StudentReview->setStudent(new Student())->setComment('comment')->setRating(3)->setCourse(new SessionDayCourse());
        $this->assertInstanceOf(Student::class, $StudentReview->getStudent());
        $this->assertEquals('comment', $StudentReview->getComment());
        $this->assertEquals(3, $StudentReview->getRating());
        $this->assertInstanceOf(SessionDayCourse::class, $StudentReview->getCourse());
    }

}
