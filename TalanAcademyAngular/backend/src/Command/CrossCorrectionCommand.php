<?php

namespace App\Command;

use App\Event\NoInstructionsEvent;
use App\Service\CronService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CrossCorrectionCommand
 * @package App\Command
 */
class CrossCorrectionCommand extends Command
{
    protected static $defaultName = 'app:cross-correction';
    /**
     * @var CronService
     */
    private $cronService;

    public function __construct(CronService $cronService, ?string $name = null)
    {
        parent::__construct($name);
        $this->cronService = $cronService;
    }

    protected function configure()
    {
        $this
            ->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
            $this->cronService->launchCrossCorrections();
            $io->success('La répartition de corrections a été faite avec succès.');
    }
}
