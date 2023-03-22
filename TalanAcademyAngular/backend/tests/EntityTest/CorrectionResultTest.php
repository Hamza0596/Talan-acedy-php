<?php

namespace App\Tests\EntityTest;

use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\SessionOrder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CorrectionResultTest extends KernelTestCase
{

    public function testCorrectionResultCreate()
    {
        $correctionResult = new CorrectionResult();
        $correctionResult->setResult(true);

        $order = new SessionOrder();
        $correctionResult->setOrderCourse($order);
        $correction = new Correction();
        $correctionResult->setCorrection($correction);

        $this->assertEquals(null, $correctionResult->getId());
        $this->assertEquals(true, $correctionResult->getResult());
        $this->assertInstanceOf(SessionOrder::class, $correctionResult->getOrderCourse());
        $this->assertInstanceOf(Correction::class, $correctionResult->getCorrection());
    }


}
