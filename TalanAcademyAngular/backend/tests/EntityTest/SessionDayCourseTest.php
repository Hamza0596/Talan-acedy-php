<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 16/05/2019
 * Time: 11:45
 */

namespace App\Tests\EntityTest;


use App\Entity\ActivityCourses;
use App\Entity\SessionActivityCourses;
use App\Entity\SessionDayCourse;
use App\Entity\SessionModule;
use App\Entity\SessionOrder;
use App\Entity\SessionResources;
use App\Entity\StudentReview;
use App\Entity\SubjectDayContent;
use App\Entity\SubmissionWorks;
use PHPUnit\Framework\TestCase;

class SessionDayCourseTest extends TestCase
{

    public function testSessionDayCourseCreate()
    {
        $day = new SessionDayCourse();
        $day->setStatus(SessionDayCourse::NORMAL_DAY);
        $day->setOrdre(1);
        $day->setSynopsis('Bienvenue dans ce premier vrai jour de PHP ! ');
        $day->setDescription('Installation de XAMPP, introduction à Apache et integration de PHP dans une page web');
        $day->setReference('ref_activity521124587');
        $module = new SessionModule();
        $module->setTitle('Introduction PHP');
        $day->setModule($module);
        $resource1 = new SessionResources();
        $resource2 = new SessionResources();
        $resource1->setTitle('Les bases du langage PHP ');
        $day->addResource($resource1);
        $day->addResource($resource2);
        $day->removeResource($resource2);


        $order1 = new SessionOrder();
        $order2 = new SessionOrder();
        $day->addOrder($order1);
        $day->addOrder($order2);
        $day->removeOrder($order2);

        $activity1 = new SessionActivityCourses();
        $activity2 = new SessionActivityCourses();
        $activity1->setTitle('activity 1');
        $day->addActivityCourses($activity1);
        $day->addActivityCourses($activity2);
        $day->removeActivityCourses($activity2);

        $studentReview1 = new StudentReview();
        $studentReview2 = new StudentReview();
        $day->addApprentice($studentReview1);
        $day->addApprentice($studentReview2);
        $day->removeApprentice($studentReview2);

        $submissionWorks1 = new SubmissionWorks();
        $submissionWorks2 = new SubmissionWorks();
        $day->addSubmissionWork($submissionWorks1);
        $day->addSubmissionWork($submissionWorks2);
        $day->removeSubmissionWork($submissionWorks2);
        $subjectDayContent1 = new SubjectDayContent();
        $subjectDayContent2 = new SubjectDayContent();
        $day->addSubjectDayContent($subjectDayContent1);
        $day->addSubjectDayContent($subjectDayContent2);
        $day->removeSubjectDayContent($subjectDayContent1);
        $day->setDateDay(new \DateTime('14-11-2019'));


        $this->assertEquals(SessionDayCourse::NORMAL_DAY, $day->getStatus());
        $this->assertEquals('1', $day->getOrdre());
        $this->assertEquals('Bienvenue dans ce premier vrai jour de PHP ! ', $day->getSynopsis());
        $this->assertEquals('Installation de XAMPP, introduction à Apache et integration de PHP dans une page web', $day->getDescription());
        $this->assertEquals('ref_activity521124587', $day->getReference());
        $this->assertInstanceOf(SessionModule::class, $day->getModule());
        $this->assertInstanceOf(SessionActivityCourses::class, $day->getActivityCourses()[0]);
        $this->assertInstanceOf(SessionResources::class, $day->getResources()[0]);
        $this->assertInstanceOf(SessionOrder::class, $day->getOrders()[0]);
        $this->assertEquals('Les bases du langage PHP ', $day->getResources()[0]->getTitle());
        $this->assertEquals('activity 1', $day->getActivityCourses()[0]->getTitle());
        $this->assertEquals(1, $day->getResources()->count());
        $this->assertEquals(1, $day->getActivityCourses()->count());
        $this->assertEquals(1, $day->getSubjectDayContents()->count());
        $this->assertEquals(1, $day->getOrders()->count());
        $this->assertEquals(new \DateTime('14-11-2019'), $day->getDateDay());
        $this->assertEquals(null, $day->getId());
        $this->assertInstanceOf(StudentReview::class, $day->getApprentices()[0]);
        $this->assertInstanceOf(SubmissionWorks::class, $day->getSubmissionWorks()[0]);
        $this->assertEquals([
            'reference' => $day->getReference(),
            'ordre' => $day->getOrdre(),
            'description' => $day->getDescription(),
            'status' => $day->getStatus(),
            'synopsis' => $day->getSynopsis(),
        ], $day->toArray());

        $day2 = new SessionDayCourse(
            [
                'ref' => 'ref123',
                'order' => 1,
                'description' => 'test',
                'status' => 'test',
                'synopsis' => 'test'
            ]
        );
    }

}
