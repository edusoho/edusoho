<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SetBackStageCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('set:backstage')
            ->addArgument('is_v2', InputArgument::OPTIONAL, '是否新后台')
            ->addArgument('allow_show_switch_btn', InputArgument::OPTIONAL, '是否显示切换按钮');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $btn = $input->getArgument('allow_show_switch_btn');
        $isV2 = $input->getArgument('is_v2');
        $output->writeln('<info>'.sprintf('设置是否新后台: %s     是否开启按钮: %s', $isV2, $btn).'</info>');

        if (empty($btn)) {
            $backstage = array('is_v2' => $isV2);
        } else {
            $backstage = array('is_v2' => $isV2, 'allow_show_switch_btn' => $btn);
        }

        $this->getSettingService()->set('backstage', $backstage);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }
}
