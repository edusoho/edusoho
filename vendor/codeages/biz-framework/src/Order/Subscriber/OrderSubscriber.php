<?php

namespace Codeages\Biz\Framework\Order\Subscriber;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'payment_trade.paid' => 'onPaid',
            'payment_trade.refunded' => 'onTradeRefunded'
        );
    }

    public function onTradeRefunded(Event $event)
    {
        $trade = $event->getSubject();
        $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);
        $this->getWorkflowService()->setRefunded($order['refund_id']);
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

    protected function getWorkflowService()
    {
        return $this->getBiz()->service('Order:WorkflowService');
    }

    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    private function getDispatcher()
    {
        $biz = $this->getBiz();
        return $biz['dispatcher'];
    }

    protected function dispatch($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }
}