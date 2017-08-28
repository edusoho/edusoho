<?php

namespace Codeages\Biz\Framework\Order\Subscriber;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Order\AbstractPaidProcessor;
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
        $this->getOrderRefundService()->setRefunded($trade['refund_id']);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);

        foreach ($orderItems as $orderItem) {
            $processor = $this->getOrderProcess($orderItem);
            if (!empty($processor)) {
                $result = $processor->process($orderItem);
                if (AbstractPaidProcessor::SUCCESS == $result) {
                    $this->getOrderService()->setOrderSuccess($order['id']);
                } else {
                    $this->getOrderService()->setOrderFail($order['id']);
                }
            }
        }
    }

    public function getOrderProcess($orderItem)
    {
        $biz = $this->getBiz();

        if (empty($biz["order_paid_processor.{$orderItem['target_type']}"])) {
            return null;
        }
        return $biz["order_paid_processor.{$orderItem['target_type']}"];
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
        $this->getOrderService()->setOrderPaid($data);
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    protected function getOrderRefundService()
    {
        return $this->getBiz()->service('Order:OrderRefundService');
    }
}