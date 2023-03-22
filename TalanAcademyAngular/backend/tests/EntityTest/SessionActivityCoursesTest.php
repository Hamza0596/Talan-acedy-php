<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 16/05/2019
 * Time: 10:53
 */

namespace App\Tests\EntityTest;


use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use PHPUnit\Framework\TestCase;

class SessionActivityCoursesTest extends TestCase
{

    public function testSessionActivityCoursesCreate()
    {
        $activitySession = new SessionActivityCourses();

        $activitySession->setTitle('test activity title')
            ->setReference('12345')
            ->setContent('test activity content')
            ->setId(1);

        $sessionday = new SessionDayCourse();
        $activitySession->setDay($sessionday);


        $this->assertEquals('test activity title',$activitySession->getTitle());
        $this->assertEquals('12345',$activitySession->getReference());
        $this->assertEquals('test activity content',$activitySession->getContent());
        $this->assertEquals(1,$activitySession->getId());
        $this->assertInstanceOf(SessionDayCourse::class,$activitySession->getDay());
        $this->assertEquals(3,count($activitySession->serializer()));
        $this->assertEquals([
            'reference' => $activitySession->getReference(),
            'title' => $activitySession->getTitle(),
            'content' => $activitySession->getContent(),
        ],$activitySession->toArray());


        $activitySession = new SessionActivityCourses([
            'title' => 'title',
            'ref' => 'ref123',
            'content' => 'content'
        ]);
    }
}