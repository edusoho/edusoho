<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloseCdnCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('cdn:close');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始关闭CDN.......</info>');

        $this->initServiceKernel();
        $cdnSetting = $this->getSettingService()->get('cdn');

        if (empty($cdnSetting) || empty($cdnSetting['enabled'])) {
            $output->writeln('<info>CDN未开启</info>');
        } else {
            $cdnSetting['enabled'] = 0;
            $this->getSettingService()->set('cdn', $cdnSetting);
            $this->getLogService()->info('system', 'update_settings', 'CDN设置', $cdnSetting);
            $output->writeln('<info>关闭成功</info>');
        }
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System:LogService');
    }
}
