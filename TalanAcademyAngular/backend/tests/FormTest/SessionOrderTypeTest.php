<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 09:54
 */

namespace App\Form;

use App\Entity\SessionOrder;
use Symfony\Component\Form\Test\TypeTestCase;

class SessionOrderTypeTest extends TypeTestCase
{


    public function testBuildForm()
    {
        $formData = [
            'description' =>'description test',
            'scale' =>1,
        ];
        $objectToCompare = new SessionOrder();
        $form = $this->factory->create(SessionOrderType::class, $objectToCompare);

        $order = new SessionOrder();
        $order->setDescription('description test')
            ->setScale(1);

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
