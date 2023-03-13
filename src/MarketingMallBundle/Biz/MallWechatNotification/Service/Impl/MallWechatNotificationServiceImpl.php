<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Service\Impl;

use Biz\BaseService;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;

class MallWechatNotificationServiceImpl extends BaseService implements MallWechatNotificationService
{
    public function notify($event, $data)
    {
        $this->dispatchEvent($event, $data);
    }
}
