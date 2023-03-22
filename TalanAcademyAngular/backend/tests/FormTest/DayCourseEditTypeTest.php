<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 23/04/2019
 * Time: 13:07
 */

namespace App\Form;


use App\Entity\DayCourse;
use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;


class DayCourseEditTypeTest extends TypeTestCase
{

    protected function getExtensions() {
        return array(new PreloadedExtension(array($this->getCKEditor()), array()));
    }
    protected function getCKEditor() {
        $configManager = $this->getMockBuilder ( CKEditorConfigurationInterface::class )->disableOriginalConstructor ()->getMock ();
        $type = new CKEditorType($configManager);

        return $type;
    }
    public function testBuildForm()
    {

        $formData = [
            'synopsis' =>'Installation & découverte de PHP, historique du langague et notions de base'
        ];
        $objectToCompare = new DayCourse();
        $form = $this->factory->create(DayCourseEditType::class, $objectToCompare);

        $dayCourse = new DayCourse();
        $dayCourse->setSynopsis('Installation & découverte de PHP, historique du langague et notions de base');

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
