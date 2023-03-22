<?php

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderCourseTest extends KernelTestCase
{
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new Module());
    }
    public function testModel()
    {
        $order =
            (new OrderCourse())
        ->setDescription('consigne 1')
        ->setScale(2)
        ->setRef("order_exemple_1554299083")
                ->setDeleted(1)
        ->setDayCourse(new DayCourse());
        $this->assertInstanceOf(DayCourse::class, $order->getDayCourse());
        $this->assertEquals('consigne 1', $order->getDescription());
        $this->assertEquals(2, $order->getScale());
        $this->assertEquals('order_exemple_1554299083', $order->getRef());
        $this->assertEquals(3,count($order->serializer()));
        $this->assertEquals(null, $order->getId());
        $this->assertEquals(1, $order->getDeleted());

    }

    public function testCustomValidator()
    {
        $this->assertEquals(3, $this->validator->count());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[0]->getMessage());
    }

}
