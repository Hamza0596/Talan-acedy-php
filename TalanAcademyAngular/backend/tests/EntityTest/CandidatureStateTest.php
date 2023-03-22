<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 21/06/2019
 * Time: 08:19
 */

namespace App\Tests\EntityTest;


use App\Entity\Candidature;
use App\Entity\CandidatureState;
use PHPUnit\Framework\TestCase;

class CandidatureStateTest extends TestCase
{
    public function testCandidatureStateCreate()
    {
        $candidatureState = new CandidatureState();
        $candidatureState->getId();
        $candidature = new Candidature();
        $candidature->setStatus(Candidature::NOUVEAU);
        $candidatureState->setStatus($candidature->getStatus());
        $this->assertEquals(Candidature::NOUVEAU, $candidatureState->getStatus());
        $candidatureState->setTitle('test');
        $this->assertEquals('test', $candidatureState->getTitle());
        $candidatureState->setDescription('test');
        $this->assertEquals('test', $candidatureState->getDescription());
       $candidatureState->setDate(new \DateTime('2019-04-23'));
        $this->assertEquals(new \DateTime('2019-04-23'), $candidatureState->getDate());

        $candidatureState->setCandidature($candidature);
        $this->assertEquals($candidature, $candidatureState->getCandidature());


    }
}