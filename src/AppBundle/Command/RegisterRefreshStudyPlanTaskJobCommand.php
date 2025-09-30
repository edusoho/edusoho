<?php

namespace AppBundle\Command;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterRefreshStudyPlanTaskJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('register-job:refresh-study-plan-task');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $job = $this->getSchedulerService()->getJobByName('RefreshStudyPlanTaskJob');
        if (!empty($job)) {
            $output->writeln('<info>Job已经注册过了</info>');

            return;
        }
        $this->getSchedulerService()->register([
            'name' => 'RefreshStudyPlanTaskJob',
            'expression' => '0 3 * * *',
            'class' => 'AgentBundle\Biz\StudyPlan\Job\RefreshStudyPlanTaskJob',
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
