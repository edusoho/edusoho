<?php

namespace AppBundle\Command;

use Biz\System\Service\SettingService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCloudSearchSettingTypeCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:add-cloud-search-type');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchSetting = $this->getSettingService()->get('cloud_search');
        if (empty($searchSetting)) {
            return;
        }

        $searchSetting['type'] = [
            'course' => 1,
            'classroom' => 1,
            'itemBankExercise' => 1,
            'teacher' => 1,
            'thread' => 1,
            'article' => 1,
        ];

        $this->getSettingService()->set('cloud_search', $searchSetting);
        $output->writeln('<info>添加类型成功</info>');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
