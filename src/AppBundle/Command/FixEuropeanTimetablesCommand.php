<?php

namespace AppBundle\Command;

use AppBundle\Service\ToolsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class FixEuropeanTimetablesCommand  extends Command
{


    /**
     * @var ToolsService
     */
    private $tools;

    public function __construct(ToolsService $tools)
    {
        $this->tools = $tools;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:fix-european-timetables');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $this->tools->fixEuropeantimetables();
       $output->writeln( "Ok");
    }

}