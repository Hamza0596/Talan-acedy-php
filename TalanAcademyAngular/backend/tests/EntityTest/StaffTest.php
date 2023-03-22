<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:36
 */

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class StaffTest extends TestCase
{

    public function testStaffCreate()
    {
        $staff = new Staff();
        $staff->setFunction('mentor');
        $this->assertEquals('mentor',$staff->getFunction());
        $cursus=new Cursus();
        $staff->setCursus($cursus);
        $this->assertEquals($cursus, $staff->getCursus());
        $staff->setStatus(null);
        $this->assertEquals(null, $staff->getStatus());
        $sessionMentor=new SessionMentor();
        $staff->addSessionMentor($sessionMentor);
        $staff->removeSessionMentor($staff->getSessionMentors()[0]);
        $mentorsAppreciation = new MentorsAppreciation();
        $mentorsAppreciation1 = new MentorsAppreciation();
        $staff->addMentorsAppreciation($mentorsAppreciation);
        $staff->addMentorsAppreciation($mentorsAppreciation1);
        $staff->removeMentorsAppreciation($mentorsAppreciation1);
        $this->assertInstanceOf(MentorsAppreciation::class, $staff->getMentorsAppreciations()[0]);
        $student = new Student();
        $student->setFirstName('student test');
        $staff->setStudent($student);
        $this->assertEquals('student test', $staff->getStudent()->getFirstName());


    }

}
