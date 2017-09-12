<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class RefundingOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $order = $this->changeStatus(self::NAME);
        if(empty($order['trade_sn'])) {
            return $order;
        }

        $this->getPayService()->applyRefundByTradeSn($order['trade_sn']);
        return $order;
    }

    public function refunded($data = array())
    {
        return $this->getOrderStatus(RefundedOrderStatus::NAME)->process($data);
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }
}