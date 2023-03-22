<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 10/02/2020
 * Time: 14:21
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ResourceRecommendationTest extends KernelTestCase
{

    public function testOrder()
    {
        $resouceRecommendation = new ResourceRecommendation();
        $resouceRecommendation->setScore(1);
        $resource = new SessionResources();
        $resource->setTitle('resource to recommend');
        $apprentice = new Student();
        $apprentice->setFirstName('apprentice');
        $resource->addResourceRecommendation($resouceRecommendation);
        $resouceRecommendation->setResource($resource)
                              ->setApprentice($apprentice);
        $this->assertEquals(null,$resouceRecommendation->getId());
        $this->assertEquals('resource to recommend', $resouceRecommendation->getResource()->getTitle());
        $this->assertEquals('apprentice',$resouceRecommendation->getApprentice()->getFirstName());
        $this->assertEquals(1,$resouceRecommendation->getScore());
    }


}
