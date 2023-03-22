<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 14:11
 */

namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class UserTypeTest extends TypeTestCase
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
            'firstName' => 'john',
            'lastName' => 'doe',
            'email' => 'admin@talan.com',
            'password' => password_hash('talan12345', PASSWORD_BCRYPT)
        ];
        $objectToCompare = new User();
        $form = $this->factory->create(UserType::class, $objectToCompare);

        $user = new User();
        $user->setFirstName('john')
            ->setLastName('doe')
            ->setEmail('admin@talan.com');
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
