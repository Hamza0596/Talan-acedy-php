<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 21/06/2019
 * Time: 15:03
 */

namespace App\Tests\Command;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CrossCorrectionTest extends WebTestCase
{

    public function testCrossCorrection()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('app:cross-correction');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testJokersRetrait()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('jokers-retrait');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);
        $commandTester1 = new CommandTester($command);
        $commandTester1->execute([
            'command' => $command->getName()
        ]);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

}
