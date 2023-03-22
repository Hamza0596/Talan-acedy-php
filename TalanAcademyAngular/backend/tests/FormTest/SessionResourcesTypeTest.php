<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 09:14
 */

namespace App\Form;

use App\Entity\SessionResources;
use Symfony\Component\Form\Test\TypeTestCase;

class SessionResourcesTypeTest extends TypeTestCase
{


    public function testBuildForm()
    {
        $formData = [
            'ref' =>'ffffffffffffffffffffffffffffffffffffff',
            'title' =>'introduction php',
            'url' =>'https://stackoverflow.com/questions/12184376/how-to-pass-twig-parameters-through-formbuilders-fields',
//            'ResourcesOwner' =>'admin',
        ];
        $objectToCompare = new SessionResources();
        $form = $this->factory->create(SessionResourcesType::class, $objectToCompare);

        $resources = new SessionResources();
        $resources->setRef('ffffffffffffffffffffffffffffffffffffff')
            ->setUrl('https://stackoverflow.com/questions/12184376/how-to-pass-twig-parameters-through-formbuilders-fields')
//            ->setResourcesOwner('admin')
            ->setTitle('introduction php');

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