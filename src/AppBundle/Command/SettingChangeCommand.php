<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SettingChangeCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('setting:change')
            ->addArgument('name', InputArgument::OPTIONAL, '设置名称 ')
            ->addArgument('key', InputArgument::OPTIONAL, '键')
            ->addArgument('value', InputArgument::OPTIONAL, '值');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $settingName = $input->getArgument('name');
        $settingKey = $input->getArgument('key');
        $settingValue = $input->getArgument('value');
        $output->writeln('<info>'.sprintf('设置setting名称: %s   键: %s  值: %s', $settingName, $settingKey, $settingValue).'</info>');
        if (is_null($settingName) || is_null($settingKey) || is_null($settingValue)) {
            return;
        }

        $setting = $this->getSettingService()->get($settingName, []);
        $setting[$settingKey] = $settingValue;
        $this->getSettingService()->set($settingName, $setting);
        $output->writeln('<info>设置成功</info>');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
