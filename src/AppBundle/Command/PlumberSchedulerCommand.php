<?php

namespace AppBundle\Command;

use Biz\Plumber\Queue\QueueFactory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlumberSchedulerCommand extends BaseCommand
{
    protected $client;

    protected function configure()
    {
        $this->setName('util:plumber-scheduler')
            ->setDescription('执行plumber的定时任务');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getBiz()->offsetGet('s2b2c.merchant.logger');
        $logger->info('Crontab:开始将定时任务放入plumber');
        try {
            $queue = $this->getCrontabJobQueue();

            $jobId = Uuid::uuid1();

            $queueJobId = $queue->putJob($jobId, 'crontab_job_worker', 'Put job into worker');

            if (false === $queueJobId) {
                $logger->info('Crontab:定时任务放入队列失败');
            }

            $logger->info("Crontab:定时任务# {$jobId} 放入plumber成功");
        } catch (\Exception $e) {
            $logger->error('Crontab:定时任务放入队列出错 error:'.$e->getMessage().' TraceString:'.$e->getTraceAsString());
        }

        return true;
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
}
