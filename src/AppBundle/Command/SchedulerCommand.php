<?php

namespace AppBundle\Command;

use Codeages\Beanstalk\Client;
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

        try {
            if (empty($model)) {
                return $this->putIntoPlumber();
            }

            if ('exec' != $model) {
                return true;
            }

            return $this->doScheduleJob();
        } catch (\Error $error) {
            $this->getBiz()->offsetGet('s2b2c.merchant.logger')
                ->error('Crontab:定时任务执行出错 error:'.$error->getMessage().' TraceString:'.$error->getTraceAsString());

            return true;
        }
    }

    protected function putIntoPlumber()
    {
        $logger = $this->getBiz()->offsetGet('s2b2c.merchant.logger');
        $logger->info('Crontab:开始将定时任务放入plumber');

        if ($this->isTubeBusy('crontab_job_worker')) {
            $logger->info('Crontab:'.'crontab_job_worker is busy, will put next time, data:');

            return true;
        }

        $client = $this->getBeanstalkClient();
        $client->useTube('crontab_job_worker');

        $body = [
            'RETRY_TIMES' => 0,
            'options' => [
                'dbConfig' => $this->getBeanstalkConfig(),
                'requestTime' => time(),
            ],
        ];

        $client->put(
            500,
            0,
            60,
           json_encode($body)
        );

        $queueJobId = $client->disconnect();

        if (false === $queueJobId) {
            $logger->info('Crontab:定时任务，放入队列失败', ['DATA' => $body]);
        }

        $logger->info("Crontab:定时任务{$queueJobId}放入plumber成功");

        return true;
    }

    protected function getBeanstalkConfig()
    {
        $plumberQueueDatabaseConfig = $this->getServiceKernel()->getParameter('plumber_queue_databases');
        $config['host'] = empty($plumberQueueDatabaseConfig['crontab_job_queue']) ? '' : $plumberQueueDatabaseConfig['crontab_job_queue']['host'];
        $config['port'] = empty($plumberQueueDatabaseConfig['crontab_job_queue']) ? '' : $plumberQueueDatabaseConfig['crontab_job_queue']['port'];
        $config['persistent'] = false;

        return $config;
    }

    protected function getBeanstalkClient()
    {
        $client = empty($this->client) ? new Client($this->getBeanstalkConfig()) : $this->client;
        $client->connect();

        return $client;
    }

    protected function isTubeBusy($topic)
    {
        $tubeStatus = $this->getTubeStatus($topic);

        if (!empty($tubeStatus) && !empty($tubeStatus['current-jobs-ready'])) {
            return true;
        }

        return false;
    }

    public function getTubeStatus($tube)
    {
        try {
            $client = $this->getBeanstalkClient();

            return $client->statsTube($tube);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function doScheduleJob()
    {
        $logger = $this->getBiz()->offsetGet('logger');

        $logger->info('Crontab:开始执行定时任务');

        $this->setDisableWebCrontab();
        $this->initServiceKernel();
        $this->getSchedulerService()->execute();

        $logger->info('Crontab:定时任务执行完毕');
    }

    protected function setDisableWebCrontab()
    {
        $setting = $this->getSettingService()->get('magic', []);
        if (empty($setting['disable_web_crontab'])) {
            $setting['disable_web_crontab'] = 1;
            $this->getSettingService()->set('magic', $setting);
        }
    }

    protected function getSchedulerService()
    {
        return $this->getServiceKernel()->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
