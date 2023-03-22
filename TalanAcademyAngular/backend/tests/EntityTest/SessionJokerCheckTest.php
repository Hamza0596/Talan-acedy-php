<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:36
 */

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class SessionJokerCheckTest extends TestCase
{

    public function testStaffCreate()
    {
        $sessionJokercheck = new SessionJokerCheck();
        $session = new Session();
        $sessionJokercheck->setAverage(5)
            ->setCorrection('test')
            ->setSessionJokerCheck($session)
            ->setSubmittedWork('test');

        $this->assertEquals(null,$sessionJokercheck->getId());
        $this->assertEquals(5,$sessionJokercheck->getAverage());
        $this->assertEquals('test',$sessionJokercheck->getCorrection());
        $this->assertEquals('test',$sessionJokercheck->getSubmittedWork());
        $this->assertInstanceOf(Session::class,$sessionJokercheck->getSessionJokerCheck());
    }

}
