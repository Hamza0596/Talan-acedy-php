<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 14:38
 */

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class UserEditEmailTypeTest extends TypeTestCase
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
           'email'=>'admin@talan.com'
        ];
        $objectToCompare = new User();
        $form = $this->factory->create(UserEditEmailType::class, $objectToCompare);
        $user = new User();
        $user->setEmail('admin@talan.com');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($user, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
