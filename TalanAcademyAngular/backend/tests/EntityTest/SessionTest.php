<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 24/04/2019
 * Time: 14:42
 */

namespace App\Tests\EntityTest;

use App\Entity\Cursus;
use App\Entity\Session;
use App\Entity\SessionJokerCheck;
use App\Entity\SessionMentor;
use App\Entity\SessionModule;
use App\Entity\SessionUserData;
use App\Entity\Student;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testSessionCreate()
    {
        $session = new Session();
        $session->setHMaxCorection(12)
            ->setHMaxSubmit(0)
            ->setJokerNbr(3)
            ->setNbrValidation(2)
            ->setOrdre(5)
            ->setPercentageOrder(50)
            ->setNbrCandidats(30)
            ->setStartDate(new \DateTime('2019-04-25'))
            ->setEndDate(new \DateTime('2019-07-25'))
            ->setStatus('en cours')
            ->setMoy(3.2)
            ->setDaysNumber(40);
        $sessionMentor1 = new SessionMentor();
        $sessionMentor2 = new SessionMentor();
        $session->addSessionMentor($sessionMentor1);
        $session->addSessionMentor($sessionMentor2);
        $session->removeSessionMentor($sessionMentor2);


        $cursus = new Cursus();
        $session->setCursus($cursus);

        $sessionModule1 = new SessionModule();
        $sessionModule2 = new SessionModule();
        $session->addModule($sessionModule1);
        $session->addModule($sessionModule2);
        $session->removeModule($sessionModule2);

        $SessionJokerCheck1 = new SessionJokerCheck();
        $SessionJokerCheck2 = new SessionJokerCheck();
        $session->addSessionJokerCheck($SessionJokerCheck1);
        $session->addSessionJokerCheck($SessionJokerCheck2);
        $session->removeSessionJokerCheck($SessionJokerCheck2);

        $sessionUser = new SessionUserData();
        $sessionUser2 = new SessionUserData();
        $session->addSessionUserData($sessionUser);
        $session->addSessionUserData($sessionUser2);
        $session->removeSessionUserData($sessionUser2);


        $this->assertEquals(null, $session->getId());
        $this->assertEquals(30, $session->getNbrCandidats());
        $this->assertEquals(12, $session->getHMaxCorection());
        $this->assertEquals(0, $session->getHMaxSubmit());
        $this->assertEquals(2, $session->getNbrValidation());
        $this->assertEquals(5, $session->getOrdre());
        $this->assertEquals(50, $session->getPercentageOrder());
        $this->assertEquals(3, $session->getJokerNbr());
        $this->assertEquals(new \DateTime('2019-04-25'), $session->getStartDate());
        $this->assertEquals(new \DateTime('2019-07-25'), $session->getEndDate());
        $this->assertEquals('en cours', $session->getStatus());
        $this->assertEquals(3.2, $session->getMoy());
        $this->assertEquals(40, $session->getDaysNumber());
        $this->assertEquals('25-04-2019', $session->getName());
        $this->assertInstanceOf(Cursus::class, $session->getCursus());
        $this->assertEquals(1, $session->getModules()->count());
        $this->assertInstanceOf(SessionModule::class, $session->getModules()[0]);
        $this->assertEquals(1, $session->getSessionUserDatas()->count());
        $this->assertInstanceOf(SessionUserData::class, $session->getSessionUserDatas()[0]);
        $this->assertEquals(1, $session->getSessionMentors()->count());
        $this->assertInstanceOf(SessionMentor::class, $session->getSessionMentors()[0]);
        $this->assertEquals(1, $session->getSessionJokerChecks()->count());
        $this->assertInstanceOf(SessionJokerCheck::class, $session->getSessionJokerChecks()[0]);
    }

}
