<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SetEnableAntiBrushCaptchaCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('setting:change')
            ->addArgument('settingName', InputArgument::OPTIONAL, '设置名称 ')
            ->addArgument('key', InputArgument::OPTIONAL, '键')
            ->addArgument('value', InputArgument::OPTIONAL, '值');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $settingName = $input->getArgument('settingName');
        $settingKey = $input->getArgument('key');
        $settingValue= $input->getArgument('value');
        $output->writeln('<info>'.sprintf('设置setting名称: %s   键: %s  值: %s', $settingName, $settingKey, $settingValue).'</info>');
        if (!isset($settingName) || !isset($settingKey) || !isset($settingValue)) {
            return;
        }

        $setting = $this->getSettingService()->get($settingName, array());
        $setting[$settingKey] = $settingValue;
        $output->writeln('设置成功');
        $this->getSettingService()->set($settingName, $setting);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
