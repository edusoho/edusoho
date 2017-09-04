<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class AuditingStatus extends AbstractRefundStatus
{
    const NAME = 'auditing';

    public function getName()
    {
        return self::NAME;
    }

    public function refunding($data)
    {
        return $this->getOrderRefundStatus(RefundingStatus::NAME)->process($data);
    }

    public function refused($data)
    {
        return $this->getOrderRefundStatus(RefusedStatus::NAME)->process($data);
    }

    public function cancel()
    {
        return $this->getOrderRefundStatus(CancelStatus::NAME)->process();
    }
}