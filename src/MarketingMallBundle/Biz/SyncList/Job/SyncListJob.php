<?php

namespace MarketingMallBundle\Biz\SyncList\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;
use MarketingMallBundle\Client\MarketingMallClient;

class SyncListJob extends AbstractJob
{

    public function execute()
    {
        $tasks = $this->getSyncListService()->getSyncType();
        if (empty($tasks)) {
            return;
        }
        $client = new MarketingMallClient($this->biz);
        $flag = false;
        foreach ($tasks as $task)
        {
            $result = $client->syncNotify($task['type']);
            if ($result['ok'])
            {
                $flag = true;
            }
        }
        if ($flag)
        {
            $ids = implode(',',array_column($this->getSyncListService()->getSyncIds(),'id'));
            $this->getSyncListService()->syncStatusUpdate($ids);
        }
    }

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->biz->service('MarketingMallBundle:SyncList:SyncListService');
    }
}
