<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 21/06/2019
 * Time: 08:49
 */

namespace App\Tests\EntityTest;


use App\Entity\Cursus;
use App\Entity\SessionDayCourse;
use App\Entity\Student;
use App\Entity\SubmissionWorks;
use PHPUnit\Framework\TestCase;

class SubmissionWorksTest extends TestCase
{
    public function testSubmissionWork(){
        $submisisonWork=new SubmissionWorks();
        $submisisonWork->getId();
        $sessionDayCourse=new SessionDayCourse();
        $submisisonWork->setCourse($sessionDayCourse);
        $this->assertEquals($sessionDayCourse, $submisisonWork->getCourse());
        $student=new Student();
        $submisisonWork->setStudent($student);
        $this->assertEquals($student, $submisisonWork->getStudent());
        $submisisonWork->setRepoLink('https://www.gitlab.com');
        $submisisonWork->setRepoLink(base64_encode('https://www.gitlab.com'));
        $this->assertEquals('https://www.gitlab.com', $submisisonWork->getRepoLink());


    }

}