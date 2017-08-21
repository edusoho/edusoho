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
            'trade.refunded' => 'onTradeRefunded'
        );
    }

    public function onTradeRefunded(Event $event)
    {
        $trade = $event->getSubject();
        $data = $event->getArguments();
        $this->getOrderRefundService()->finishRefund($trade['refund_id']);
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $processor = $this->getOrderProcess($order);
        if (!empty($processor)) {
            $result = $processor->process($order);
            if (AbstractPaidProcessor::SUCCESS == $result) {
                $this->getOrderService()->finishOrder($order['id']);
            }
        }
    }

    public function getOrderProcess($order)
    {
        $biz = $this->getBiz();;
        if (empty($biz["order_paid_processor.{$order['type']}"])) {
            return null;
        }
        return $biz["order_paid_processor.{$order['type']}"];
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