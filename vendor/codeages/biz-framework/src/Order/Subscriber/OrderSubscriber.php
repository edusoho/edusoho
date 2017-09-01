<?php

namespace Codeages\Biz\Framework\Order\Subscriber;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Order\Callback\PaidCallback;
use Codeages\Biz\Framework\Util\ArrayToolkit;
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
        $orderItems = $order['items'];
        $deducts = $order['deducts'];
        unset($order['items']);
        unset($order['deducts']);

        $indexedOrderItems = ArrayToolkit::index($orderItems, 'id');
        foreach ($deducts as $deduct) {
            $deduct['order'] = $order;
            if (!empty($indexedOrderItems[$deduct['item_id']])) {
                $deduct['item'] = $indexedOrderItems[$deduct['item_id']];
            }

            $processor = $this->getDeductPaidCallback($deduct);
            if (!empty($processor)) {
                $processor->paidCallback($deduct);
            }
        }

        $results = array();
        foreach ($orderItems as $orderItem) {
            $orderItem['order'] = $order;

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

    protected function getDeductPaidCallback($deduct)
    {
        $biz = $this->getBiz();

        if (empty($biz["order.deduct.{$deduct['deduct_type']}"])) {
            return null;
        }
        return $biz["order.deduct.{$deduct['deduct_type']}"];
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