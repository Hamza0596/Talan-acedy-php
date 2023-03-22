<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 25/02/2020
 * Time: 14:36
 */

namespace App\Command;


use App\Entity\Session;
use App\Service\SessionVsCursusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SessionVersusCursusCommand extends Command
{
    protected static $defaultName = 'app:sessionVscursus';

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var SessionVsCursusService
     */
    private $service;

    public function __construct(EntityManagerInterface $manager, SessionVsCursusService $service)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->service = $service;
    }

    protected function configure()
    {
        $this->setDescription('')
            ->addArgument('session', InputArgument::REQUIRED, 'session id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $io =  new SymfonyStyle($input,$output);
       $sessionId = $input->getArgument('session');
       $session = $this->manager->getRepository(Session::class)->find($sessionId);
       if (!$session) {
           $io->error('La session d \'id '. $sessionId. ' n\'existe pas');
       }


       else{
           $this->service->sessionVsCursusV2($session);
           $io->success('a message was sent to you');
       }
    }
}
