<?php

namespace Codeages\Biz\Order\Status\Refund;

interface RefundStatus
{
    public function refunding($data);

    public function refused($data);

    public function refunded($data);

    public function cancel();
}