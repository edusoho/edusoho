<?php

namespace AppBundle\Command;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterPushEveningLearnNoticeJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('register-job:push-evening-learn-notice');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getSchedulerService()->register([
            'name' => 'PushEveningLearnNoticeJob',
            'expression' => '0 20 * * *',
            'class' => 'AgentBundle\Biz\StudyPlan\Job\PushEveningLearnNoticeJob',
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
