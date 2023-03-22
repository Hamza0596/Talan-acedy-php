<?php

namespace App\Tests\EntityTest;

use App\Entity\MentorsAppreciation;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use PHPUnit\Framework\TestCase;

class MentorsAppreciationTest extends TestCase
{
    public function testComment()
    {
        $mentorsAppreciation = new MentorsAppreciation();
        $staff= new Staff();
        $sessionUser= new SessionUserData();
        $mentorsAppreciation
            ->setAnnouncedBy('MAKNI Mouna php')
            ->setCreatedBy('MAKNI Mouna php')
            ->setCreatedAt(new \DateTime('2019-07-25'))
            ->setComment('this is a staff appreciation comment')
            ->setStaff($staff)
            ->setSessionUser($sessionUser);
        $this->assertEquals(null, $mentorsAppreciation->getId());
        $this->assertEquals('MAKNI Mouna php', $mentorsAppreciation->getCreatedBy());
        $this->assertEquals('MAKNI Mouna php', $mentorsAppreciation->getAnnouncedBy());
        $this->assertInstanceOf(SessionUserData::class, $mentorsAppreciation->getSessionUser());
        $this->assertInstanceOf(Staff::class, $mentorsAppreciation->getStaff());
        $this->assertEquals(new \DateTime('2019-07-25'), $mentorsAppreciation->getCreatedAt());
        $this->assertEquals('this is a staff appreciation comment', $mentorsAppreciation->getComment());
    }
}
