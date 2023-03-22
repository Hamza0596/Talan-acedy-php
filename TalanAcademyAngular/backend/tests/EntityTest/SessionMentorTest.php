<?php

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class SessionMentorTest extends TestCase
{
    public function testSessionMentorCreate()
    {
        $sessionMentor = new SessionMentor();

        $session = new Session();
        $sessionMentor->setSession($session);
        $sessionMentor->setStatus('ACTIVE');
        $mentor = new Staff();
        $sessionMentor->setMentor($mentor);

        $this->assertEquals(SessionMentor::ACTIVE, $sessionMentor->getStatus());
        $this->assertInstanceOf(Session::class, $sessionMentor->getSession());
        $this->assertInstanceOf(Staff::class, $sessionMentor->getMentor());


    }

}
