<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 29/03/2019
 * Time: 14:31
 */

namespace App\Form;


use App\Entity\Student;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class StudentTypeTest extends TypeTestCase
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
            'tel' =>'21212212',
            'city' =>'Tunis',
        ];

        $moduleToCompare = new Student();
        $form = $this->factory->create(StudentType::class, $moduleToCompare);

        $student = new Student();
        $student->setLastName('doe');
        $student->setFirstName('john');
        $student->setTel('21212212');
        $student->setCity('Tunis');
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($student, $moduleToCompare);

        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

}
