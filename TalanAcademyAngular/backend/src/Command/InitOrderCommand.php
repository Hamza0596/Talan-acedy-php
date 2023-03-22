<?php

namespace App\Command;

use App\Entity\Cursus;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitOrderCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class InitOrderCommand extends Command
{
    protected static $defaultName = 'app:init-order';
    /**
     * @var SessionService
     */
    private $sessionService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * InitOrderCommand constructor.
     * @param SessionService $sessionService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SessionService $sessionService, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->sessionService = $sessionService;
        $this->entityManager = $entityManager;
    }


    protected function configure()
    {
        $this
            ->setDescription('initialiser l\'ordre des sessions')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all_cursus = $this->entityManager->getRepository(Cursus::class)->findAll();
        foreach ($all_cursus as $cursus) {
            $this->sessionService->updateOrdre($cursus, $this->entityManager);
        }
    }
}
