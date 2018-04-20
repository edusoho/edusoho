<?php

namespace Biz\Distributor\Event;

use AppBundle\Common\ArrayToolkit;
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
        $context = $event->getSubject();

        $orderIdItems = ArrayToolkit::index($context['items'], 'order_id');
        $orderIds = array_keys($orderIdItems);

        $orders = $this->getOrderService()->findOrdersByIds($orderIds);

        $userIds = ArrayToolkit::column($orders, 'user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $distributorProductOrders = array();  //商品分销订单
        $distributorUserOrders = array();     //用户拉新订单
        foreach ($orders as $order) {
            if (!empty($orderIdItems[$order['id']]['create_extra']['distributorToken'])) {
                $distributorProductOrders[] = $order;
            } elseif (!empty($users[$order['user_id']])) {
                $user = $users[$order['user_id']];
                if ('distributor' == $user['type']) {
                    $distributorUserOrders[] = $order;
                }
            }
        }
        $this->getDistributorOrderService()->batchCreateJobData($distributorUserOrders);
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
