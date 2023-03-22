<?php

namespace App\Command;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\CalculateAverageService;
use App\Service\CronService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\StreamedResponse;


/**
 * Class testCmd
 * @package App\Command
 * @codeCoverageIgnore
 */
class TestCommand extends Command
{
    protected static $defaultName = 'app:test';
    /**
     * @var CronService
     */
    private $service;
    private $userRepository;
    private $sessionRepository;

    public function __construct(CalculateAverageService $service, UserRepository $userRepository, SessionRepository $sessionRepository, ?string $name = null)
    {
        parent::__construct($name);
        $this->service = $service;
        $this->userRepository = $userRepository;
        $this->sessionRepository =$sessionRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('')
            ->addArgument('userId', InputArgument::OPTIONAL, 'Argument description')
            ->addArgument('sessionId', InputArgument::OPTIONAL, 'Argument description');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $userId = $input->getArgument('userId');
        $sessionId = $input->getArgument('sessionId');

        $user = $this->userRepository->find($userId);
        $session = $this->sessionRepository->find($sessionId);
        $this->service->calculateMinMaxScore($session,$user);
    }


}
