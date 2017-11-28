<?php

namespace Codeages\Biz\Order\Status\Order;

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
            'pay_time',
            'payment',
            'paid_cash_amount',
            'paid_coin_amount',
        ));

        $order = $this->getOrderDao()->getBySn($data['order_sn'], array('lock' => true));
        $order = $this->payOrder($order, $data);
        $order['items'] = $this->payOrderItems($order);

        $deducts = $this->getOrderItemDeductDao()->findByOrderId($this->order['id']);
        foreach ($deducts as $key => $deduct) {
            $deducts[$key] = $this->getOrderItemDeductDao()->update($deduct['id'], array(
                'status' => self::NAME
            ));
        }

        $order['deducts'] = $deducts;
        return $order;
    }

    protected function payOrder($order, $data)
    {
        $data = ArrayToolkit::parts($data, array(
            'trade_sn',
            'pay_time',
            'payment',
            'paid_cash_amount',
            'paid_coin_amount',
        ));
        $data['status'] = PaidOrderStatus::NAME;
        $data['refund_deadline'] = empty($order['expired_refund_days']) ? 0 : $data['pay_time'] + $order['expired_refund_days']*86400;
        return $this->getOrderDao()->update($order['id'], $data);
    }

    protected function payOrderItems($order)
    {
        $items = $this->getOrderItemDao()->findByOrderId($order['id']);
        $fields = ArrayToolkit::parts($order, array('status'));
        $fields['pay_time'] = $order['pay_time'];
        foreach ($items as $key => $item) {
            $items[$key] = $this->getOrderItemDao()->update($item['id'], $fields);
        }
        return $items;
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }

    public function fail($data = array())
    {
        return $this->getOrderStatus(FailOrderStatus::NAME)->process($data);
    }

    public function refunding($data = array())
    {
        return $this->getOrderStatus(RefundingOrderStatus::NAME)->process($data);
    }
}