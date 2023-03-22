<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:37
 */

namespace App\Entity;


use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function testUserCreate()
    {
        $user = new User();
        $resource = new Resources();
        $user->setEmail('admin@talan.com')
            ->setNewEmail('admin@talan.com')
            ->setFirstName('john')
            ->setLastName('doe')
            ->setPassword('talan12345')
            ->setIsActivated(true)
            ->setImage(null)
            ->setToken('testTokenResetPasswordInactiveAccount')
            ->setRoles([User::ROLE_INSCRIT])
            ->setRegistrationDate(new \DateTime('2019-06-03'))
            ->addResource($resource);

        $this->assertEquals($resource, $user->getResources()[0]);

        $user->removeResource($resource);


        $sessionUserData1 = new SessionUserData();
        $sessionUserData2 = new SessionUserData();
        $user->addSessionUserData($sessionUserData1);
        $user->addSessionUserData($sessionUserData2);
        $user->removeSessionUserData($sessionUserData2);

        $candidatePreparcours1 = new PreparcoursCandidate();
        $candidatePreparcours2 = new PreparcoursCandidate();
        $user->addCandidatepreparcour($candidatePreparcours1);
        $user->addCandidatepreparcour($candidatePreparcours2);
        $user->removeCandidatepreparcour($candidatePreparcours1);

        $this->assertEquals(null, $user->getId());
        $this->assertEquals(null, $user->getSalt());
        $this->assertEquals('admin@talan.com', $user->getEmail());
        $this->assertEquals('admin@talan.com', $user->getUsername());
        $this->assertEquals('admin@talan.com', $user->getNewEmail());
        $this->assertEquals('doe john', $user->getFullName());
        $this->assertEquals(new \DateTime('2019-06-03'), $user->getRegistrationDate());
        $this->assertEquals('john', $user->getFirstName());
        $this->assertEquals('doe', $user->getLastName());
        $this->assertEquals('talan12345', $user->getPassword());
        $this->assertTrue($user->getIsActivated());
        $this->assertNull($user->getImage());
        $this->assertEquals('testTokenResetPasswordInactiveAccount', $user->getToken());
        $this->assertEquals(User::ROLE_INSCRIT, $user->getRoles()[0]);
        $this->assertEquals(1, $user->getSessionUserDatas()->count());
        $this->assertEquals(1, $user->getCandidatepreparcours()->count());
        $this->assertInstanceOf(SessionUserData::class, $user->getSessionUserDatas()[0]);


    }
}
