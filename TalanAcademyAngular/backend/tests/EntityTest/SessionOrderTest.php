<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 15/05/2019
 * Time: 15:17
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SessionOrderTest extends KernelTestCase
{
    private $validator;
    private $order;


    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new SessionOrder());
    }

    public function testModel()
    {
        $order = new SessionOrder();
       $order->setDayCourse(new SessionDayCourse())
            ->setDescription('Resources 5')
            ->setScale(1)
            ->setRef('Resources_Luca_1554299083');

        $CorrectionResult1 = new CorrectionResult();
        $CorrectionResult2 = new CorrectionResult();
        $order->addCorrectionResult($CorrectionResult1);
        $order->addCorrectionResult($CorrectionResult2);
        $order->removeCorrectionResult($CorrectionResult2);
        $this->assertInstanceOf(SessionOrder::class, $order);
        $this->assertInstanceOf(SessionDayCourse::class, $order->getDayCourse());
        $this->assertEquals('Resources 5', $order->getDescription());
        $this->assertEquals(1, $order->getScale());
        $this->assertEquals('Resources_Luca_1554299083', $order->getRef());
        $this->assertEquals(0, $order->getId());
        $this->assertEquals(1,$order->getCorrectionResults()->count());
        $this->assertEquals(3,count($order->serializer()));
    }

    public function testCustomValidator()
    {
        $this->assertEquals(1, $this->validator->count());
        $this->assertEquals('La description ne peut pas être null !!!', $this->validator[0]->getMessage());
    }

}
