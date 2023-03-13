<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;

class MallWechatNotificationEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'groupon.create' => 'onGrouponCreate',
            'groupon.join' => 'onGrouponJoin',
            'groupon.fail' => 'onGrouponFail',
            'groupon.success' => 'onGrouponSuccess',
            'groupon.order.refund' => 'onGrouponOrderRefund',
        ];
    }

    public function onGrouponCreate(Event $event)
    {

    }

    public function onGrouponJoin(Event $event)
    {

    }

    public function onGrouponFail(Event $event)
    {

    }

    public function onGrouponSuccess(Event $event)
    {

    }

    public function onGrouponOrderRefund(Event $event)
    {

    }
}
