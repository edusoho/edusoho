<?php

namespace AppBundle\Command;

use Biz\Plumber\Queue\QueueFactory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestPutJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('test:put-job')
            ->addArgument('topic', InputArgument::REQUIRED, 'job topic')
            ->addArgument('body', InputArgument::REQUIRED, 'job body');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = '{"productId":2}';

        $logger = $this->getBiz()->offsetGet('s2b2c.merchant.logger');

        $topic = $input->getArgument('topic');
        $output->writeln("<info>开始创建队列任务 {$topic}.</info>");

        $queue = $this->getQueueByWorkerTopic($topic);

        $jobId = Uuid::uuid1();

        $body = $input->getArgument('body');

        if (json_decode($body, true)) {
            $body = json_decode($body, true);
        }

        $queueJobId = $queue->putJob($jobId, $topic, $body);

        if (false === $queueJobId) {
            $logger->info('创建队列任务失败');
            $output->writeln('<error>创建队列任务失败.</error>');
        }

        $logger->info('创建队列任务成功');
        $output->writeln('<info>创建队列任务成功.</info>');
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

    protected function getQueueByWorkerTopic($topic)
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

        if (empty($plumberQueues['workers']) || empty($plumberQueues['workers'][$topic])) {
            return ['queue' => null, 'message' => 'Workers config not exist.'];
        }

        $worker = $plumberQueues['workers'][$topic];

        $config = empty($plumberQueueOptions[$worker['queue']]) ? $defaultConfig : $plumberQueueOptions[$worker['queue']];

        return QueueFactory::create($config['type'], $config);
    }
}
