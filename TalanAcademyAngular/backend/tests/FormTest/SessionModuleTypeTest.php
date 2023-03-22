<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 10:03
 */

namespace App\Form;

use App\Entity\SessionModule;
use Symfony\Component\Form\Test\TypeTestCase;

class SessionModuleTypeTest extends TypeTestCase
{


    public function testBuildForm()
    {
        $formData = [
            'title' =>'day title',
            'description' =>'day description',
        ];
        $objectToCompare = new SessionModule();
        $form = $this->factory->create(SessionModuleType::class, $objectToCompare);

        $modules = new SessionModule();
        $modules->setDescription('day description');
        $modules->setTitle('day title');
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($modules, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }


}