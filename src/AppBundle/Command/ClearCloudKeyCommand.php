<?php

namespace AppBundle\Command;

use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCloudKeyCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('cloud:clear-key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storageSetting = $this->getSettingService()->get('storage');
        $output->writeln("<info>当前AccessKey: {$storageSetting['cloud_access_key']}</info>");
        $output->writeln("<info>当前SecretKey: {$storageSetting['cloud_secret_key']}</info>");
        $storageSetting['cloud_access_key'] = '';
        $storageSetting['cloud_secret_key'] = '';
        $storageSetting['cloud_key_applied'] = 0;

        $this->getSettingService()->set('storage', $storageSetting);
        $this->getCacheService()->clear('cloud_status');
        $output->writeln('<info>清除key成功!</info>');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CacheService
     */
    private function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
