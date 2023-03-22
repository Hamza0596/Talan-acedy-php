<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 17/05/2019
 * Time: 09:28
 */

namespace App\Form;


use App\Entity\SessionActivityCourses;
use App\Tests\EntityTest\SessionActivityCoursesTest;
use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class SessionActivityTypeTest extends TypeTestCase
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
        $activityCursusToCompare = new SessionActivityCourses();
        $form = $this->factory->create(SessionActivityType::class, $activityCursusToCompare);
        $activityCursus = new SessionActivityCourses();
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
