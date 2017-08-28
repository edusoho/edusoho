<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

use Codeages\Biz\Framework\Util\ArrayToolkit;

class PaidOrderStatus extends AbstractOrderStatus
{
    const NAME = 'paid';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $data = ArrayToolkit::parts($data, array(
            'order_sn',
            'trade_sn',
            'pay_time'
        ));

        $order = $this->getOrderDao()->getBySn($data['order_sn'], array('lock' => true));
        $order = $this->payOrder($order, $data);
        $this->payOrderItems($order);
        return $order;
    }

    protected function payOrder($order, $data)
    {
        $data = ArrayToolkit::parts($data, array(
            'trade_sn',
            'pay_time'
        ));
        $data['status'] = PaidOrderStatus::NAME;
        return $this->getOrderDao()->update($order['id'], $data);
    }

    protected function payOrderItems($order)
    {
        $items = $this->getOrderItemDao()->findByOrderId($order['id']);
        $fields = ArrayToolkit::parts($order, array('status'));
        $fields['pay_time'] = $order['pay_time'];
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], $fields);
        }
    }

    public function getPriorStatus()
    {
        return array(PayingOrderStatus::NAME);
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }

    public function fail($data = array())
    {
        return $this->getOrderStatus(FailOrderStatus::NAME)->process($data);
    }
}