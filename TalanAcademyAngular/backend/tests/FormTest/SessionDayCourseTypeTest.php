<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 09:36
 */

namespace App\Form;

use App\Entity\SessionDayCourse;
use Symfony\Component\Form\Test\TypeTestCase;

class SessionDayCourseTypeTest extends TypeTestCase
{


    public function testBuildForm()
    {
        $formData = [
            'description' =>'day description',
             ];
        $objectToCompare = new SessionDayCourse();
        $form = $this->factory->create(SessionDayCourseType::class, $objectToCompare);

        $resources = new SessionDayCourse();
        $resources->setDescription('day description');
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($resources, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }


}