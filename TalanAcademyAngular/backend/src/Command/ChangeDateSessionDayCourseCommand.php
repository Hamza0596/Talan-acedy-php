<?php


namespace App\Command;


use App\Service\AssociateDateService;
use App\Service\SessionService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
/**
 * Class ChangeDateSessionDayCourseCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class ChangeDateSessionDayCourseCommand extends Command
{
    protected static $defaultName = 'change-date';

    private $sessionService;
    private $associateDateService;
    private $manager;

    public function __construct(SessionService $sessionService, AssociateDateService $associateDateService, ObjectManager $manager, string $name = null)
    {
        parent::__construct($name);
        $this->sessionService = $sessionService;
        $this->associateDateService = $associateDateService;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this->setDescription('change date of session day course');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->sessionService->updateDateSessionDayCourse($this->associateDateService,$this->manager);
        $io->success('Le changement des dates est effectué avec succès');
    }

}
