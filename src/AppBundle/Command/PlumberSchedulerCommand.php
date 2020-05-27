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
        $output->writeln('<info>开始将定时任务放入plumber</info>');

        try {
            $queue = $this->getCrontabJobQueue();

            $jobId = Uuid::uuid1();

            $queueJobId = $queue->putJob($jobId, 'crontab_job_worker', 'Put job into worker');

            if (false === $queueJobId) {
                $logger->info('Crontab:定时任务放入队列失败');
                $output->writeln('<error>定时任务放入队列失败</error>');
            }

            $logger->info("Crontab:定时任务# {$jobId} 放入plumber成功");
            $output->writeln("<info>定时任务# {$jobId} 放入plumber成功\"</info>");
        } catch (\Exception $e) {
            $logger->error('Crontab:定时任务放入队列出错 error:'.$e->getMessage().' TraceString:'.$e->getTraceAsString());
            $output->writeln("<error>定时任务放入队列出错 error:{$e->getMessage()}, TraceString:{$e->getTraceAsString()}</error>");
        }

        return true;
    }

    protected function getConfig()
    {
        $configFilePath = $this->getContainer()->getParameter('kernel.root_dir').'/config/plumber.php';
        if (!$this->getContainer()->hasParameter('plumber_queues_options') || !file_exists($configFilePath)) {
            return [];
        }

        return [
            include "{$configFilePath}",
            $this->getContainer()->getParameter('plumber_queues_options'),
        ];
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

        list($plumberQueues, $plumberQueueOptions) = $this->getConfig();
        if (empty($plumberQueues) || empty($plumberQueueOptions)) {
            return ['queue' => null, 'message' => 'Config not exist in plumber.php or parameters.yml.'];
        }

        if (empty($plumberQueues['workers']) || empty($plumberQueues['workers']['crontab_job_worker'])) {
            return ['queue' => null, 'message' => 'Workers config not exist.'];
        }

        $worker = $plumberQueues['workers']['crontab_job_worker'];

        $config = empty($plumberQueueOptions[$worker['queue']]) ? $defaultConfig : $plumberQueueOptions[$worker['queue']];

        return QueueFactory::create($config['type'], $config);
    }
}
