<?php

namespace App\Command;

use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * Class testCmd
 * @package App\Command
 * @codeCoverageIgnore
 */
class CopyModuleCommand extends Command
{
    protected static $defaultName = 'app:copy:module';

    private $moduleRepository;
    private $entityManager;

    public function __construct(ModuleRepository $moduleRepository, EntityManagerInterface $entityManager, ?string $name = null)
    {
        parent::__construct($name);
       $this->moduleRepository = $moduleRepository;
       $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a full copy of an existing cursus module')
            ->addArgument('sourceModuleId', InputArgument::REQUIRED, 'Source Module')
            ->addArgument('destinationModuleId', InputArgument::REQUIRED, 'Destination Module');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $sourceModuleId = $input->getArgument('sourceModuleId');
        $destinationModuleId = $input->getArgument('destinationModuleId');

        $sourceModule = $this->moduleRepository->find($sourceModuleId);
        if(!$sourceModule){
            $io->error("Source module not found !");
            exit;
        }

        $destinationModule = $this->moduleRepository->find($destinationModuleId);
        if(!$destinationModule){
            $io->error("Destination module not found !");
            exit;
        }

        foreach ($sourceModule->getDayCourses() as $dayCourse){
            $io->comment("Creating day [".$dayCourse->getId()."] : ".$dayCourse->getDescription());
            $newCreatedDay = clone $dayCourse;
            $newCreatedDay->setReference('day_' . time() . mt_rand());
            $this->entityManager->persist($newCreatedDay);
            $resources = $dayCourse->getResources();
            foreach ($resources as $resource){
                $io->comment("Creating Ressource [".$resource->getId()."] : ".$resource->getTitle());
                $newCreatedResource = clone $resource;
                $newCreatedResource->setDay($newCreatedDay);
                $newCreatedResource->setRef("resource_" . time() . "_" . mt_rand());
                $this->entityManager->persist($newCreatedResource);
                $newCreatedDay->addResource($newCreatedResource);
            }
            $activities = $dayCourse->getActivityCourses();
            foreach ($activities as $activity){
                $io->comment("Creating Activity [".$activity->getId()."] : ".$activity->getTitle());
                $newCreatedActivity = clone $activity;
                $newCreatedActivity->setDay($newCreatedDay);
                $newCreatedActivity->setReference("activity_" . time() . "_" . mt_rand());
                $this->entityManager->persist($newCreatedActivity);
                $newCreatedDay->addActivityCourses($activity);
            }
            $orders = $dayCourse->getOrders();
            foreach ($orders as $order){
                $io->comment("Creating Order [".$order->getId()."] : ".$order->getDescription());
                $newCreatedOrder = clone $order;
                $newCreatedOrder->setRef("order_" . time() . "_" . mt_rand());
                $this->entityManager->persist($newCreatedOrder);
                $newCreatedDay->addOrder($newCreatedOrder);
            }
            $destinationModule->addDayCourse($newCreatedDay);
            $this->entityManager->flush();
        }


    }


}
