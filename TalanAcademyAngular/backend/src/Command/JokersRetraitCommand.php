<?php

namespace App\Command;

use App\Service\JokerSubtractionService;
use App\Service\SessionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class JokersRetraitCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class JokersRetraitCommand extends Command
{
    protected static $defaultName = 'jokers-retrait';
    const RETRAIT ='retrait';

    /**
     * @var SessionService
     */
    private $sessionService;
    /**
     * @var JokerSubtractionService
     */
    private $jokerSubtractionService;

    public function __construct(SessionService $sessionService, JokerSubtractionService $jokerSubtractionService, ?string $name = null)
    {
        parent::__construct($name);
        $this->sessionService = $sessionService;
        $this->jokerSubtractionService = $jokerSubtractionService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption(self::RETRAIT, null, InputOption::VALUE_OPTIONAL, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $user_id = $input->getOption(self::RETRAIT);
        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }
        $this->jokerSubtractionService->noSubmittedWork();
        $this->jokerSubtractionService->noCorrectionMade();
        $this->jokerSubtractionService->lessThanAverage();
        if ($input->getOption(self::RETRAIT)) {
            $this->sessionService->retraitJokerFromUser($user_id);
            $io->success('Le retrait de joker a été fait avec succès');
        }
    }
}
