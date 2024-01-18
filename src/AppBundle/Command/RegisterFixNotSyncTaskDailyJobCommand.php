<?php

namespace AppBundle\Command;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterFixNotSyncTaskDailyJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('register-job:fix-not-sync-task');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSchedulerService()->register([
            'name' => 'FixNotSyncTaskJob_Daily',
            'expression' => '0 0 * * *',
            'class' => 'Biz\Task\Job\FixNotSyncTaskJob',
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
