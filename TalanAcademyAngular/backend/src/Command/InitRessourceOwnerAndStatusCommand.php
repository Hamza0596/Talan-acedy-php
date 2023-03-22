<?php

namespace App\Command;

use App\Entity\Resources;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitRessourceOwnerAndStatusCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class InitRessourceOwnerAndStatusCommand extends Command
{
    protected static $defaultName = 'app:initResourceOwnerAndStatus';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;

    }

    protected function configure()
    {
        $this
            ->setDescription('Initialiser ressourceOwner et status ')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allResources = $this->entityManager->getRepository(Resources::class)->findAll();
        foreach ($allResources as $resource) {
            $resource->setStatus('approved');
            $resource->setResourceOwner($this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@talan.com']));
            $this->entityManager->persist($resource);
            $this->entityManager->flush();
        }

    }
}
