<?php

namespace Codeages\Biz\Framework\Order\Callback;

interface PaidCallback
{
    const SUCCESS = 'success';

    public function paidCallback($subject);
}
