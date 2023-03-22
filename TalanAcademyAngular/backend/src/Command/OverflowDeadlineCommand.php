<?php


namespace App\Command;


use App\Service\OverflowDeadlineService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class OverflowDeadlineCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class OverflowDeadlineCommand extends Command
{
    protected static $defaultName = 'overflow-deadline';

    private $deadlineService;

    public function __construct(OverflowDeadlineService $deadlineService, ?string $name = null)
    {
        parent::__construct($name);
        $this->deadlineService = $deadlineService;
    }

    protected function configure()
    {
        $this
            ->setDescription('verify overflow deadline');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->deadlineService->verifyOverflowDeadline();
        $io->success('La vérification est effectuée avec succès');
    }
}
