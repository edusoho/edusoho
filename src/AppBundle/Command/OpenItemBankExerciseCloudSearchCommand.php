<?php

namespace AppBundle\Command;

use Biz\Search\Constant\CloudSearchType;
use Biz\System\Service\SettingService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenItemBankExerciseCloudSearchCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:open-itemBankExercise-cloud-search');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchSetting = $this->getSettingService()->get('cloud_search');
        if (empty($searchSetting)) {
            return;
        }

        $searchSetting['type'] = [
            CloudSearchType::COURSE => $searchSetting['type']['course'],
            CloudSearchType::CLASSROOM => $searchSetting['type']['classroom'],
            CloudSearchType::ITEM_BANK_EXERCISE => 1,
            CloudSearchType::TEACHER => $searchSetting['type']['teacher'],
            CloudSearchType::THREAD => $searchSetting['type']['thread'],
            CloudSearchType::ARTICLE => $searchSetting['type']['article'],
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
