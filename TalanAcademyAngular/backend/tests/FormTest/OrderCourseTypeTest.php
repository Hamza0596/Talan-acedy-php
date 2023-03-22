<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 16:10
 */

namespace App\Form;


use App\Entity\OrderCourse;
use Symfony\Component\Form\Test\TypeTestCase;

class OrderCourseTypeTest extends TypeTestCase
{
    public function testBuildForm()
    {
        $formData = [
            'description' => 'description description description',
            'scale' => 1,
        ];
        $objectToCompare = new OrderCourse();
        $form = $this->factory->create(OrderCourseType::class, $objectToCompare);
        $order = new OrderCourse();
        $order->setScale(1)
            ->setDescription('description description description');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($order, $objectToCompare);
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
