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
        $order = $event->getSubject();
        foreach ($order['items'] as $item) {
            $order = $this->getOrderService()->getOrder($item['order_id']);
            if (!empty($order)) {
                $user = $this->getUserService()->getUser($order['user_id']);
                if (!empty($user) && 'distributor' == $user['type']) {
                    $this->getDistributorOrderService()->createJobData($order);
                }
            }
        }
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
