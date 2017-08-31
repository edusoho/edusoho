<?php

namespace Codeages\Biz\Framework\Order\Subscriber;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Order\Callback\PaidCallback;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'pay.success' => 'onPaid',
            'order.paid' => 'onOrderPaid',
            'payment_trade.refunded' => 'onTradeRefunded'
        );
    }

    public function onTradeRefunded(Event $event)
    {
        $trade = $event->getSubject();
        $this->getWorkflowService()->setRefunded($trade['refund_id']);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        $results = array();
        foreach ($orderItems as $orderItem) {
            $processor = $this->getProductPaidCallback($orderItem);
            if (!empty($processor)) {
                $results[] = $processor->paidCallback($orderItem);
            }
        }

        if (in_array(PaidCallback::SUCCESS, $results) && count($results) == 1) {
            $this->getWorkflowService()->finish($order['id']);
        } else if (count($results) > 0){
            $this->getWorkflowService()->fail($order['id']);
        }
    }

    protected function getProductPaidCallback($orderItem)
    {
        $biz = $this->getBiz();

        if (empty($biz["order.product.{$orderItem['target_type']}"])) {
            return null;
        }
        return $biz["order.product.{$orderItem['target_type']}"];
    }

    public function onPaid(Event $event)
    {
        $trade = $event->getSubject();
        $args = $event->getArguments();
        $data = array(
            'trade_sn' => $trade['trade_sn'],
            'pay_time' => $args['paid_time'],
            'order_sn' => $trade['order_sn']
        );
        $this->getWorkflowService()->paid($data);
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getWorkflowService()
    {
        return $this->getBiz()->service('Order:WorkflowService');
    }


    protected function getOrderRefundService()
    {
        return $this->getBiz()->service('Order:OrderRefundService');
    }
}