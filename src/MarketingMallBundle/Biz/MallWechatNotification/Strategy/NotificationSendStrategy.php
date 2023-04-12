<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Strategy;

use MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent\NotificationEvent;

interface NotificationSendStrategy
{
    public function send(NotificationEvent $event, array $data);
}
