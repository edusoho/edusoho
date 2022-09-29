<?php

namespace MarketingMallBundle\Biz\SyncList\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;
use MarketingMallBundle\Client\MarketingMallClient;

class SyncListJob extends AbstractJob
{

    public function execute()
    {
        $task = $this->getSyncListService()->getSyncType();
        if (empty($task)){
            return;
        }
        $client = new MarketingMallClient($this->biz);
        $result = $client->syncNotify('classroom');

        if($result == true) {
            $this->getSyncListService()->updateSyncType();
        }

        file_put_contents("/tmp/aa/jc.txt", var_export($result, true));
    }

    /**
     * @return SyncListService
     */
    protected function getSyncListService()
    {
        return $this->biz->service('MarketingMallBundle:SyncList:SyncListService');
    }
}
