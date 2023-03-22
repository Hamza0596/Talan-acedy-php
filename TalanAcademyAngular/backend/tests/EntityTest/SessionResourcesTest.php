<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 15/05/2019
 * Time: 14:48
 */

namespace App\Entity;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SessionResourcesTest extends KernelTestCase
{
    public function testCreate()
    {
      $ressources = new SessionResources(['title' => 'test', 'ref' => 'test', 'url' => base64_encode('https://symfony.com'),'comment'=>'test comment']);
      $ressources->setDay(new SessionDayCourse())
                ->setTitle('Resources 5')
                ->setRef('Resources_Luca_1554299083')
                ->setUrl('https://symfony.com/doc/current/reference/constraints/Url.html');
      $resourceRecommendation1 = new ResourceRecommendation();
      $resourceRecommendation2 = new ResourceRecommendation();
      $ressources->addResourceRecommendation($resourceRecommendation1);
      $ressources->addResourceRecommendation($resourceRecommendation2);
      $ressources->removeResourceRecommendation($resourceRecommendation2);



        $this->assertInstanceOf(SessionResources::class, $ressources);
        $this->assertInstanceOf(SessionDayCourse::class, $ressources->getDay());
        $this->assertEquals(null, $ressources->getId());
        $this->assertEquals('Resources 5', $ressources->getTitle());
        $this->assertEquals('Resources_Luca_1554299083', $ressources->getRef());
        $this->assertEquals('https://symfony.com/doc/current/reference/constraints/Url.html', $ressources->getUrl());
        $this->assertEquals(3,count($ressources->serializer()));
        $this->assertEquals([
            'ref' => $ressources->getRef(),
            'title' => $ressources->getTitle(),
            'url' => $ressources->getUrl(),
            'comment' => $ressources->getComment(),
        ],$ressources->toArray());
        $this->assertEquals(1,$this->count($ressources->getResourceRecommendations()));

    }

}
