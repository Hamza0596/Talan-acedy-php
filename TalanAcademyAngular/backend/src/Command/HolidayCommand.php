<?php

namespace App\Command;

use App\Repository\YearPublicHolidaysRepository;
use App\Service\HolidaysService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HolidayCommand extends Command
{
    protected static $defaultName = 'app:holiday';


    private $holidaysService;

    private $manager;

    private $yearPublicHolidaysRepository;

    public function __construct(HolidaysService $holidaysService, ObjectManager $manager,YearPublicHolidaysRepository $yearPublicHolidaysRepository)
    {
        parent::__construct();
        $this->holidaysService = $holidaysService;
        $this->manager = $manager;
        $this->yearPublicHolidaysRepository = $yearPublicHolidaysRepository;
    }


    protected function configure()
    {
        $this
            ->setDescription('Add recurrent holidays')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $add = $this->holidaysService->addHolidaysToNewYear($this->yearPublicHolidaysRepository, $this->manager);
        if (count($add)>0) {
            $io->success('Les jours fériés sont ajoutés.');
        }
        else{
            $io->success('Pas de jours fériés à ajouter.');
        }
    }
}
