<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 04/04/2019
 * Time: 10:56
 */

namespace App\Tests\FormTest;

use App\Entity\Module;
use App\Form\ModuleType;
use Symfony\Component\Form\Test\TypeTestCase;

class ModuleTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'title' => 'testTitle',
            'description' => 'testDescription',

        ];

        $moduleToCompare = new Module();
        $form = $this->factory->create(ModuleType::class, $moduleToCompare);

        $module = new Module();
        $module->setTitle('testTitle');
        $module->setDescription('testDescription');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($module, $moduleToCompare);

        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}