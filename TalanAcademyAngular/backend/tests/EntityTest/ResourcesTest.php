<?php

namespace App\Tests\EntityTest;

use App\Entity\DayCourse;
use App\Entity\Resources;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ResourcesTest extends KernelTestCase
{

    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new Resources());
    }

    public function testModel()
    {
        $resource = new Resources();
        $resource->setStatus('approved');
        $user = new User();
        $day = new DayCourse();
        $resource->setDay(new DayCourse())
            ->setTitle('Resources 5')
            ->setResourceOwner($user)
            ->setRef('Resources_Luca_1554299083')
            ->setUrl(base64_encode('https://symfony.com/doc/current/reference/constraints/Url.html'))
            ->setDeleted(1)
            ->setComment('Cette ressource est utile')
            ->setUrl('https://symfony.com/doc/current/reference/constraints/Url.html');
        $resource->getId();
        $resource->serializer();
        $this->assertEquals($day, $resource->getDay());
        $this->assertEquals('Resources 5', $resource->getTitle());
        $this->assertEquals('https://symfony.com/doc/current/reference/constraints/Url.html', $resource->getUrl());
        $this->assertEquals('approved', $resource->getStatus());
        $this->assertEquals($user, $resource->getResourceOwner());
        $this->assertEquals('Resources_Luca_1554299083', $resource->getRef());
        $this->assertEquals(1, $resource->getDeleted());
        $this->assertEquals('Cette ressource est utile', $resource->getComment());
        $this->assertEquals([
            'ref' => $resource->getRef(),
            'title' => $resource->getTitle(),
            'url' => $resource->getUrl(),
            'comment' => $resource->getComment(),
        ], $resource->toArray());

    }

    public function testCustomValidator()
    {
        $this->assertEquals(1, $this->validator->count());
        $this->assertEquals('Cette valeur ne doit pas être vide.', $this->validator[0]->getMessage());
    }
}
