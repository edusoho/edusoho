<?php

namespace Biz\RefererLog\Event;

use Biz\RefererLog\Service\OrderRefererLogService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\PluginBundle\Event\EventSubscriber;

class OrderRefererLogEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.paid' => 'onOrderPaid',
            'order.created' => 'onOrderCreated',
        );
    }

    public function onOrderCreated(Event $event)
    {
        global $kernel;

        if (empty($kernel)) {
            return false;
        }

        $uv = $kernel->getContainer()->get('request')->cookies->get('uv');

        $token = $this->getRefererLogService()->getOrderRefererByUv($uv);

        if (empty($token)) {
            return false;
        }
        $order = $event->getSubject();
        $orderIds = explode('|', trim($token['orderIds'], '|'));
        array_push($orderIds, $order['id']);

        $token['orderIds'] = '|'.implode($orderIds, '|').'|';

        $this->getRefererLogService()->updateOrderReferer($token['id'], $token);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $orderItem = reset($orderItems);

        $token = $this->getRefererLogService()->getOrderRefererLikeByOrderId($order['id']);

        if (empty($token) || $order['price_amount'] == 0) {
            return false;
        }

        $refererOrderIds = array_values($token['data']);

        $refererLogs = $this->getRefererLogService()->searchRefererLogs(
            array('ids' => $refererOrderIds),
            array('createdTime' => 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$refererLogs) {
            return false;
        }

        foreach ($refererLogs as $key => $refererLog) {
            $fields = array(
                'refererLogId' => $refererLog['id'],
                'orderId' => $order['id'],
                'sourceTargetId' => $refererLog['targetId'],
                'sourceTargetType' => $refererLog['targetType'],
                'targetType' => $orderItem['target_type'],
                'targetId' => $orderItem['target_id'],
                'createdUserId' => $order['user_id'],
            );

            $this->getOrderRefererLogService()->addOrderRefererLog($fields);

            $this->getRefererLogService()->waveRefererLog($refererLog['id'], 'orderCount', 1);
        }
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderRefererLogService
     */
    protected function getOrderRefererLogService()
    {
        return $this->getBiz()->service('RefererLog:OrderRefererLogService');
    }

    /**
     * @return OrderRefererLogService
     */
    protected function getRefererLogService()
    {
        return $this->getBiz()->service('RefererLog:RefererLogService');
    }
}
