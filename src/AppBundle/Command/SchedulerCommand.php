<?php

namespace AppBundle\Command;

use Biz\Plumber\Queue\QueueFactory;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommand extends BaseCommand
{
    protected $client;

    protected function configure()
    {
        $this->setName('util:scheduler')
        ->setDescription('执行定时任务')
        ->addArgument('model', InputArgument::OPTIONAL, 'exec model: exec 执行');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        if (empty($model)) {
            return $this->putIntoPlumber();
        }

        if ('exec' != $model) {
            return true;
        }

        return $this->doScheduleJob();
    }

    protected function getCrontabJobQueue()
    {
        $defaultConfig = [
            'type' => 'beanstalk',
            'host' => '127.0.0.1',
            'port' => 11300,
            'password' => '',
            'persistent' => true,
            'timeout' => 2,
            'socket_timeout' => 20,
            'logger' => null,
        ];
        $plumberQueueOptions = $this->getContainer()->getParameter('plumber_queues_options');

        $plumberQueues = include $this->getContainer()->getParameter('kernel.root_dir').'/config/plumber.php';

        $optionKey = $plumberQueues['queues']['crontab_job_queue']['queue_options'];
        $config = empty($plumberQueueOptions[$optionKey]) ? $defaultConfig : $plumberQueueOptions[$optionKey];

        return QueueFactory::create($config['type'], $config);
    }

    protected function putIntoPlumber()
    {
        $logger = $this->getBiz()->offsetGet('s2b2c.merchant.logger');
        $logger->info('Crontab:开始将定时任务放入plumber');
        try {
            $queue = $this->getCrontabJobQueue();

            $queueJobId = $queue->putJob('crontab_job_worker', 'Put job into worker');

            if (false === $queueJobId) {
                $logger->info('Crontab:定时任务放入队列失败');
            }

            $logger->info("Crontab:定时任务{$queueJobId}放入plumber成功");
        } catch (\Exception $e) {
            $logger->error('Crontab:定时任务放入队列出错 error:'.$e->getMessage().' TraceString:'.$e->getTraceAsString());
        }

        return true;
    }

    protected function doScheduleJob()
    {
        $logger = $this->getBiz()->offsetGet('s2b2c.merchant.job.logger');

        $logger->info('Crontab:开始执行定时任务');

        try {
            $this->setDisableWebCrontab();
            $this->initServiceKernel();
            $this->getSchedulerService()->execute();

            $logger->info('Crontab:定时任务执行完毕');
        } catch (\Exception $e) {
            $logger->error('Crontab:定时任务执行出错 error:'.$e->getMessage().' TraceString:'.$e->getTraceAsString());
        }
    }

    protected function setDisableWebCrontab()
    {
        $setting = $this->getSettingService()->get('magic', []);
        if (empty($setting['disable_web_crontab'])) {
            $setting['disable_web_crontab'] = 1;
            $this->getSettingService()->set('magic', $setting);
        }
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getServiceKernel()->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
