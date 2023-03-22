<?php

namespace App\Command;

use App\Entity\Session;
use App\Entity\SessionUserData;
use App\Entity\Student;
use App\Entity\User;
use App\Service\CalculateAverageService;
use App\Service\SessionService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChangeStatusApprenticeCommand extends Command
{
    protected static $defaultName = 'app:changeStatusApprentice';
    private $manager;
    private $averageService;
    private $sessionService;

    public function __construct(ObjectManager $manager, CalculateAverageService $averageService, SessionService $sessionService)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->averageService=$averageService;
        $this->sessionService=$sessionService;
    }

    protected function configure()
    {
        $this
            ->setDescription('change the status from apprentice to qualified or eliminated ')
            ->addArgument('sessionId', InputArgument::OPTIONAL, 'Session Id')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $sessionId = $input->getArgument('sessionId');
        $this->sessionService->changeStatusApprentice($this->manager, $this->averageService,$sessionId);
        $io->success('Les statuts ont été mis à jour pour les sessions terminées');
    }
}
