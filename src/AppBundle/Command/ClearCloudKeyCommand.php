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
        $storageSetting['cloud_access_key'] = '';
        $storageSetting['cloud_secret_key'] = '';
        $storageSetting['cloud_key_applied'] = 0;

        $this->getSettingService()->set('storage', $storageSetting);
        $this->getCacheService()->clear('cloud_status');
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
