<?php

namespace AppBundle\Command;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CountOnlineCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:count-online')
            ->addArgument('type', InputArgument::REQUIRED, 'type的值是枚举类型：login, total')
            ->addArgument('minute', InputArgument::REQUIRED, '分钟');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $type = $input->getArgument('type');
        if (!in_array($type, array('login', 'total'))) {
            $output->writeln('type参数不正确，type的值是枚举类型：login, total');

            return;
        }
        $minute = $input->getArgument('minute');

        $currentTime = time();
        $start = $currentTime - $minute * 60;
        $count = 0;
        if ('login' == $type) {
            $count = $this->getStatisticsService()->countLogin($start);
        } elseif ('total' == $type) {
            $count = $this->getStatisticsService()->countOnline($start);
        }

        $output->write($count);
    }

    protected function getStatisticsService()
    {
        return ServiceKernel::instance()->createService('System:StatisticsService');
    }
}
