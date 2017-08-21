<?php

namespace Codeages\Biz\Framework\Order;

abstract class AbstractPaidProcessor
{
    const SUCCESS = 'success';

    abstract public function process($order);
}