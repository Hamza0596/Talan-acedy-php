<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 19/11/2019
 * Time: 10:33
 */

namespace App\Command;


use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\CopySessionToCursusService;
use App\Service\ExtractPropositionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CopySessionToCursusCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class CopySessionToCursusCommand extends Command
{
    protected static $defaultName = 'app:copySessionToCursus';
    /**
     * @var CopySessionToCursusService
     */
    private $copySessionToCursusService;
    /**
     * @var SessionRepository
     */
    private $sessionRepository;
    /**
     * @var ExtractPropositionService
     */
    private $service;
    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * CopySessionToCursusCommand constructor.
     * @param CopySessionToCursusService $copySessionToCursusService
     * @param SessionRepository $sessionRepository
     * @param ExtractPropositionService $service
     * @param UserRepository $userRepository
     */
    public function __construct(CopySessionToCursusService $copySessionToCursusService, SessionRepository $sessionRepository,ExtractPropositionService $service, UserRepository $userRepository)
    {
        parent::__construct();
        $this->copySessionToCursusService = $copySessionToCursusService;
        $this->sessionRepository = $sessionRepository;
        $this->service = $service;
        $this->userRepository = $userRepository;
    }
    protected function configure()
    {
        $this
            ->setDescription('')
            ->addArgument('admin', InputArgument::REQUIRED, 'admin email to set resource owner')
            ->addArgument('sessionId', InputArgument::OPTIONAL, 'session id');


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $sessionId = $input->getArgument('sessionId');
        $admin = $input->getArgument('admin');
        $io = new SymfonyStyle($input, $output);
        $user = $this->userRepository->findOneBy(['email'=>$admin]);
        if(!$user){
            $io->error('L\'utilisateur  d\'email  '.$admin.' n\'existe pas' );
        }
        elseif (in_array(User::ROLE_ADMIN,$user->getRoles())){

            if($sessionId){
                $session = $this->sessionRepository->find($sessionId);
                if($session){
                    $this->copySessionToCursusService->copyModules($session, $user);
                    $this->copySessionToCursusService->verifyCursus($session);
                    $io->success('La copie de la session d\'id '.$sessionId.' a été effectuée avec succès' );

                }
                else{
                    $io->error('La session d\'id '.$sessionId.' n\'existe pas' );
                }

            }
            else{
                $sessions = $this->sessionRepository->findAll();
                foreach ($sessions as $session){
                    $this->copySessionToCursusService->copyModules($session,$user);
                    $this->copySessionToCursusService->verifyCursus($session);

                }
                $io->success('La copie de toutes les sessions a été effectuée avec succès' );

            }
        }
        else{
            $io->error('L\'utilisateur  d\'email  '.$admin.' n\'a pas le role admin' );

        }






    }


}