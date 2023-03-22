<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 02/04/2019
 * Time: 15:51
 */

namespace App\Form;

use App\Entity\ActivityCourses;
use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class ActivityCoursesTypeTest extends TypeTestCase
{
    protected function getExtensions() {
        return array(new PreloadedExtension(array($this->getCKEditor()), array()));
    }
    protected function getCKEditor() {
        $configManager = $this->getMockBuilder ( CKEditorConfigurationInterface::class )->disableOriginalConstructor ()->getMock ();
        $type = new CKEditorType($configManager);

        return $type;
    }
    public function testSubmitValidData()
    {
        $formData = [
            'title' => 'test1',
            'content' => 'test2',
        ];
        $activityCursusToCompare = new ActivityCourses();
        $form = $this->factory->create(ActivityCoursesType::class, $activityCursusToCompare);
        $activityCursus = new ActivityCourses();
        $activityCursus->setTitle('test1')
            ->setContent("test2");
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($activityCursus, $activityCursusToCompare);

        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
