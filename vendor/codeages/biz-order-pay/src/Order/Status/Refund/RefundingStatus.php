<?php

namespace Codeages\Biz\Order\Status\Refund;

class RefundingStatus extends AbstractRefundStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function refunded($data = array())
    {
        return $this->getOrderRefundStatus(RefundedStatus::NAME)->process($data);
    }

    public function process($data = array())
    {
        $fields = array(
            'deal_time' => time(),
            'deal_user_id' => $this->biz['user']['id'],
            'deal_reason' => empty($data['deal_reason']) ? '' : $data['deal_reason'],
            'status' => self::NAME,
        );

        $fields['amount'] = 0;
        if (isset($data['refund_cash_amount'])) {
            $fields['refund_cash_amount'] = $data['refund_cash_amount'];
            $fields['amount'] = $fields['amount'] + $fields['refund_cash_amount'];
        }

        if (isset($data['refund_coin_amount'])) {
            $fields['refund_coin_amount'] = $data['refund_coin_amount'];
            $rate = $this->getCoinRate();
            $fields['amount'] = $fields['amount'] + round($fields['refund_coin_amount'] / $rate);
        }

        $orderRefund = $this->getOrderRefundDao()->update($this->orderRefund['id'], $fields);

        $orderItemRefunds = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $updatedOrderItemRefunds = array();
        foreach ($orderItemRefunds as $orderItemRefund) {
            $updatedOrderItemRefunds[] = $this->getOrderItemRefundDao()->update($orderItemRefund['id'], array(
                'status' => self::NAME,
            ));

            $this->getOrderItemDao()->update($orderItemRefund['order_item_id'], array(
                'refund_status' => self::NAME,
            ));
        }

        $orderRefund['orderItemRefunds'] = $updatedOrderItemRefunds;

        return $orderRefund;
    }

    protected function getCoinRate()
    {
        $options = $this->biz['payment.final_options'];

        return empty($options['coin_rate']) ? 1 : $options['coin_rate'];
    }
}
