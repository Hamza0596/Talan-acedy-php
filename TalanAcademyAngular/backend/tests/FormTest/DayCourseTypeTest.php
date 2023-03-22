<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 12:57
 */

namespace App\Form;


use App\Entity\DayCourse;
use Symfony\Component\Form\Test\TypeTestCase;

class DayCourseTypeTest extends TypeTestCase
{
    public function testBuildForm()
    {
        $formData = [
            'description' =>'PHP Introduction'
        ];
        $objectToCompare = new DayCourse();
        $form = $this->factory->create(DayCourseType::class, $objectToCompare);

        $dayCourse = new DayCourse();
        $dayCourse->setDescription('PHP Introduction');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($dayCourse, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

    }


}
