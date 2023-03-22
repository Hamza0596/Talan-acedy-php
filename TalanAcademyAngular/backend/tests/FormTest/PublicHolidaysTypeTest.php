<?php

namespace App\Form;

use App\Entity\PublicHolidays;
use App\Entity\User;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class PublicHolidaysTypeTest extends TypeTestCase
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
            'label' => 'test date',
            'month' => Null,
            'day' => Null,
        ];
        $objectToCompare = new PublicHolidays();
        $form = $this->factory->create(PublicHolidaysType::class, $objectToCompare);

        $holiday = new PublicHolidays();
        $holiday->setLabel('test date')
            ->setDate(Null);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($holiday, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
