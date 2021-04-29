<?php

namespace Biz\WeChat\Job;

use Biz\WeChat\Service\SubscribeRecordService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class WeChatSubscribeRecordSynJob extends AbstractJob
{
    public function execute()
    {
        $this->getSubscribeRecordService()->synchronizeSubscriptionRecords();
    }

    /**
     * @return SubscribeRecordService
     */
    protected function getSubscribeRecordService()
    {
        return $this->biz->service('WeChat:SubscribeRecordService');
    }
}
