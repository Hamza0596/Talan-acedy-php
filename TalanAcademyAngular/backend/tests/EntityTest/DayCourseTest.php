<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:35
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class DayCourseTest extends TestCase
{

    public function testDayCourseCreate()
    {
        $day = new DayCourse();
        $day->setStatus(DayCourse::NORMAL_DAY);
        $day->setOrdre(1);
        $day->setSynopsis('Bienvenue dans ce premier vrai jour de PHP ! ');
        $day->setDescription('Installation de XAMPP, introduction à Apache et integration de PHP dans une page web');
        $day->setReference('ref_activity521124587');
        $day->setDeleted(1);
        $module = new Module();
        $module->setTitle('Introduction PHP');
        $day->setModule($module);
        $resource1 = new Resources();
        $resource2 = new Resources();
        $resource1->setTitle('Les bases du langage PHP ');
        $day->addResource($resource1);
        $day->addResource($resource2);
        $day->removeResource($resource2);
        $order = new OrderCourse();
        $order2 = new OrderCourse();
        $day->addOrder($order);
        $day->addOrder($order2);
        $day->removeOrder($order2);
        $activity1 = new ActivityCourses();
        $activity2 = new ActivityCourses();
        $activity1->setTitle('activity 1');
        $day->addActivityCourses($activity1);
        $day->addActivityCourses($activity2);
        $day->removeActivityCourses($activity2);
        $this->assertEquals(DayCourse::NORMAL_DAY, $day->getStatus());
        $this->assertEquals('1', $day->getOrdre());
        $this->assertEquals(null, $day->getId());
        $this->assertEquals('Bienvenue dans ce premier vrai jour de PHP ! ', $day->getSynopsis());
        $this->assertEquals('Installation de XAMPP, introduction à Apache et integration de PHP dans une page web', $day->getDescription());
        $this->assertEquals('ref_activity521124587', $day->getReference());
        $this->assertInstanceOf(Module::class, $day->getModule());
        $this->assertInstanceOf(ActivityCourses::class, $day->getActivityCourses()[0]);
        $this->assertInstanceOf(Resources::class, $day->getResources()[0]);
        $this->assertInstanceOf(OrderCourse::class, $day->getOrders()[0]);
        $this->assertEquals('Les bases du langage PHP ', $day->getResources()[0]->getTitle());
        $this->assertEquals('activity 1', $day->getActivityCourses()[0]->getTitle());
        $this->assertEquals(1, $day->getResources()->count());
        $this->assertEquals(1, $day->getActivityCourses()->count());
        $this->assertEquals(5,count($day->serializer()));
        $this->assertEquals(1, $day->getDeleted());
        $this->assertEquals([
            'reference' => $day->getReference(),
            'ordre' => $day->getOrdre(),
            'description' => $day->getDescription(),
            'status' => $day->getStatus(),
            'synopsis' => $day->getSynopsis(),
        ], $day->toArray());
    }
}
