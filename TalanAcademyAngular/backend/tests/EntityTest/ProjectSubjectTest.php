<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 10/02/2020
 * Time: 14:42
 */

namespace App\Entity;



use PHPUnit\Framework\TestCase;

class ProjectSubjectTest extends TestCase
{

    public function testCreate(){
        $projectSubject = new ProjectSubject();
        $projectSubject->setName('subject 1')
                       ->setRef('t09980630')
                    ->setDeleted(0)
            ->setSpecification('specification test');
        $project = new Module();
        $project->setTitle('module project');
        $project->setType(Module::PROJECT);
        $projectSubject->setProject($project);
        $this->assertEquals(null,$projectSubject->getId());
        $this->assertEquals('subject 1',$projectSubject->getName());
        $this->assertEquals('t09980630',$projectSubject->getRef());
        $this->assertEquals('specification test',$projectSubject->getSpecification());
        $this->assertEquals('module project',$projectSubject->getProject()->getTitle());
        $this->assertEquals(0, $projectSubject->getDeleted());
        $this->assertEquals(3, count($projectSubject->serializer()));

    }

}
