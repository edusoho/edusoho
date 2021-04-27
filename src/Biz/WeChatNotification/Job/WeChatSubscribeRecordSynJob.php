<?php

namespace Biz\WeChatNotification\Job;

use Biz\WeChatNotification\Service\WeChatNotificationService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class WeChatSubscribeRecordSynJob extends AbstractJob
{
    public function execute()
    {
        $this->getWeChatNotificationService()->synchronizeSubscriptionRecords();
    }

    /**
     * @return WeChatNotificationService
     */
    protected function getWeChatNotificationService()
    {
        return $this->biz->service('WeChatNotification:WeChatNotificationService');
    }
}
