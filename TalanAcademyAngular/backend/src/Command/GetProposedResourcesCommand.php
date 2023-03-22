<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 25/11/2019
 * Time: 14:53
 */

namespace App\Command;


use App\Service\ExtractPropositionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetProposedResourcesCommand
 * @package App\Command
 * @codeCoverageIgnore
 */
class GetProposedResourcesCommand extends Command
{
    protected static $defaultName = 'app:getResources';

    /**
     * @var ExtractPropositionService
     */
    private $service;

    /**
     * GetProposedResourcesCommand constructor.
     * @param ExtractPropositionService $service
     */
    public function __construct(ExtractPropositionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }
    protected function configure()
    {
        $this
            ->setDescription('');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->service->sendEmailProposedResources();
    }

}