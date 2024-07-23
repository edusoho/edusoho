<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Service;

interface MallWechatNotificationService
{
    public function notify($eventName, $data);

    public function init();
}
