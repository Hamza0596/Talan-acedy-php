<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 14/06/2019
 * Time: 11:25
 */

namespace App\Command;


use App\Entity\SessionUserData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetStudentStatusCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class SetStudentStatusCommand extends Command
{
    protected static $defaultName = 'app:student-status';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * InitOrderCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


    protected function configure()
    {
        $this
            ->setDescription('initialiser le statut du candidat')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all_student = $this->entityManager->getRepository(SessionUserData::class)->findAll();
        foreach ($all_student as $student) {
            $student->setStatus('Apprenti');
            $this->entityManager->persist($student);
        }
        $this->entityManager->flush();
    }

}