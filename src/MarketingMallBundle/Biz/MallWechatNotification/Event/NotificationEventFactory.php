<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Event;

use Topxia\Service\Common\ServiceKernel;

class NotificationEventFactory
{
    /**
     * @return NotificationEvent
     */
    public static function create($eventName)
    {
        $events = [
            'grouponCreate' => GrouponCreateNotificationEvent::class,
        ];

        return new $events[$eventName](ServiceKernel::instance()->getBiz());
    }
}
