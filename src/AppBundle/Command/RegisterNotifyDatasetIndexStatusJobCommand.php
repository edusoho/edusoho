<?php

namespace AppBundle\Command;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterNotifyDatasetIndexStatusJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('register-job:notify-dataset-index-status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSchedulerService()->register([
            'name' => 'NotifyDatasetIndexStatusJob',
            'expression' => '*/30 * * * *',
            'class' => 'AgentBundle\Biz\AgentConfig\Job\NotifyDatasetIndexStatusJob',
            'args' => [],
            'misfire_threshold' => 300,
            'misfire_policy' => 'executing',
        ]);
        $output->writeln('<info>注册Job成功</info>');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
