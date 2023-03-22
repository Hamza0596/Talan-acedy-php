<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 15/08/2019
 * Time: 15:34
 */

namespace App\Tests\Command;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChangeStatusApprenticeTest extends WebTestCase
{
    public function testCrossCorrection()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('app:changeStatusApprentice');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);
        $output=$commandTester->getDisplay();
        $this->assertContains('Les statuts ont été mis à jour pour les sessions terminées', $output);
    }

}