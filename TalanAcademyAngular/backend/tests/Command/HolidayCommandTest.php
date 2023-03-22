<?php

namespace App\Tests\Command;

use App\Command\HolidayCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HolidayCommandTest extends WebTestCase
{
    public function testHoliday()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('app:holiday');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName()
        ]);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
