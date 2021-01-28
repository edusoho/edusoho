<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Common\SystemQueueCrontabinitializer;

class QueueInitCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:init')
            ->setDescription('Register plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('  DataBase消息队列初始化');
        try {
            SystemQueueCrontabinitializer::init();
            $output->writeln(' ...<info>成功</info>');
        } catch (\Exception $e) {
            $output->writeln(' ...<info>失败</info>'.$e->getMessage());
        }
    }

    /**
     * @return CloudAppDaoImpl
     */
    protected function getAppDao()
    {
        return  $this->getServiceKernel()->createDao('CloudPlatform.CloudAppDao');
    }
}
