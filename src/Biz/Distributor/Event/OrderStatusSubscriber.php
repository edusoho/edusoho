<?php

namespace Biz\Distributor\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderStatusSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.finished' => 'onOrderChangeStatus',
            'order.success' => 'onOrderChangeStatus',
            'order.refunded' => 'onOrderChangeStatus',
        );
    }

    public function onOrderChangeStatus(Event $event)
    {
        // 分销2.0 不再使用
        return true;
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getDistributorOrderService()
    {
        return $this->getBiz()->service('Distributor:DistributorOrderService');
    }
}
