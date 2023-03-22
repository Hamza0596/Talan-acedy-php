<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 14:32
 */

namespace App\Form;


use App\Entity\Staff;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class StaffTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $validator = Validation::createValidator();


        return [
            new ValidatorExtension($validator),
        ];
    }


    public function testBuildForm()
    {
        $formData = [
            'lastName' => 'doe',
            'firstName' => 'john',
            'function' =>'consultant j3'
        ];
        $moduleToCompare = new Staff();
        $form = $this->factory->create(StaffType::class, $moduleToCompare);
        $staff = new Staff();
        $staff->setLastName('doe');
        $staff->setFirstName('john');
        $staff->setFunction('consultant j3');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($staff, $moduleToCompare);
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }


}
