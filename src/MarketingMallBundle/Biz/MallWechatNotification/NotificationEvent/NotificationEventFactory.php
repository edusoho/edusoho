<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

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
            'grouponJoin' => GrouponJoinNotificationEvent::class,
            'grouponSuccess' => GrouponSuccessNotificationEvent::class,
            'grouponFail' => GrouponFailNotificationEvent::class,
            'grouponOrderRefund' => GrouponOrderRefundNotificationEvent::class,
        ];

        return new $events[$eventName](ServiceKernel::instance()->getBiz());
    }
}
