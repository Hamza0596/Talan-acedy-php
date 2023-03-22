<?php
/**
 * Created by PhpStorm.
 * User: wmhamdi
 * Date: 02/04/2019
 * Time: 11:56
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActivityCoursesTest extends KernelTestCase
{
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new ActivityCourses());
    }

    public function testModel()
    {
        $activityCursus =
            (new ActivityCourses())
                ->setId(1)
                ->setDay(new DayCourse())
                ->setTitle('activity5')
                ->setContent('contenu activity 5')
                ->setReference('ref_activity521124587')
                ->setDeleted(1);
        $this->assertEquals(1, $activityCursus->getId());
        $this->assertInstanceOf(DayCourse::class, $activityCursus->getDay());
        $this->assertEquals('activity5', $activityCursus->getTitle());
        $this->assertEquals('contenu activity 5', $activityCursus->getContent());
        $this->assertEquals('ref_activity521124587', $activityCursus->getReference());
        $this->assertEquals(1, $activityCursus->getDeleted());
        $this->assertEquals(3,count($activityCursus->serializer()));
        $this->assertEquals(['reference'=>'ref_activity521124587','title'=>'activity5','content'=>'contenu activity 5'],$activityCursus->toArray());
    }

    public function testCustomValidator()
    {
        $this->assertEquals(2, $this->validator->count());
        $this->assertEquals('Le titre ne peut pas être null !!!', $this->validator[0]->getMessage());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[1]->getMessage());
    }
}
