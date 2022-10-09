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

        foreach ($tasks as $task) {
            file_put_contents("/tmp/ab/1.txt", var_export($task, true),FILE_APPEND);
//            $result = $client->syncNotify($task);
        }


//        if($result == true) {
//            $ids = implode(',',array_column($this->getSyncListService()->getSyncIds(),'id'));
//
//            $this->getSyncListService()->syncStatusUpdate($ids);
//        }

//        file_put_contents("/tmp/aa/jc.txt", var_export($result, true));
    }

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->biz->service('MarketingMallBundle:SyncList:SyncListService');
    }
}
