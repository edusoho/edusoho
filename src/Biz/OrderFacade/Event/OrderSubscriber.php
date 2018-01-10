<?php

namespace Biz\OrderFacade\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'payment_trade.paid' => 'onPaid',
            'payment_trade.refunded' => 'onTradeRefunded',
        );
    }

    public function onTradeRefunded(Event $event)
    {
        $trade = $event->getSubject();
        $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $refundIds = ArrayToolkit::column($orderItems, 'refund_id');
        $refundIds = array_unique($refundIds);
        foreach ($refundIds as $refundId) {
            $this->getWorkflowService()->setRefunded($refundId);
        }

        $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);
        if (!empty($order)) {
            $user = $this->getUserService()->getUser($order['user_id']);
            if (!empty($user) && 'distributor' == $user['type']) {
                $this->getDistributorOrderService()->createJobData($order);
            }
        }
    }

    public function onPaid(Event $event)
    {
        $trade = $event->getSubject();
        $args = $event->getArguments();
        $data = array(
            'trade_sn' => $trade['trade_sn'],
            'pay_time' => $args['paid_time'],
            'payment' => $trade['platform'],
            'order_sn' => $trade['order_sn'],
            'paid_cash_amount' => $trade['cash_amount'],
            'paid_coin_amount' => $trade['coin_amount'],
        );

        if (0 == $trade['cash_amount'] && $trade['coin_amount'] > 0) {
            $data['payment'] = 'coin';
        }

        $this->getWorkflowService()->paid($data);

        $order = $this->getOrderService()->getOrderBySn($data['order_sn']);
        if (!empty($order)) {
            $user = $this->getUserService()->getUser($order['user_id']);
            if (!empty($user) && 'distributor' == $user['type']) {
                $this->getDistributorOrderService()->createJobData($order);
            }
        }
    }

    protected function getWorkflowService()
    {
        return $this->getBiz()->service('Order:WorkflowService');
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
