<?php

namespace Codeages\Biz\Framework\Scheduler\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('scheduler:run');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSchedulerService()->execute();
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
