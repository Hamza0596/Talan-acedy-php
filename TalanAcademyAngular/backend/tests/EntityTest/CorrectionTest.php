<?php

namespace App\Tests\EntityTest;

use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\SessionDayCourse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CorrectionTest extends KernelTestCase
{

    public function testCorrectionCreate()
    {
        $correction = new Correction();

        $correction->setComment('comment test');
        $corrector = new User();
        $correction->setCorrector($corrector);
        $corrected = new User();
        $correction->setCorrected($corrected);
        $sessionDay = new SessionDayCourse();
        $correction->setDay($sessionDay);

        $correctionResult1 = new CorrectionResult();
        $correctionResult2 = new CorrectionResult();
        $correction->addCorrectionResult($correctionResult1);
        $correction->addCorrectionResult($correctionResult2);
        $correction->removeCorrectionResult($correctionResult2);

        $this->assertEquals(null,$correction->getId());
        $this->assertEquals('comment test', $correction->getComment());
        $this->assertInstanceOf(User::class, $correction->getCorrector());
        $this->assertInstanceOf(User::class, $correction->getCorrected());
        $this->assertInstanceOf(SessionDayCourse::class, $correction->getDay());
        $this->assertInstanceOf(CorrectionResult::class,$correction->getCorrectionResults()[0]);

    }
}
