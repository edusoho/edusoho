<?php

namespace Codeages\Biz\Order\Status\Order;

interface OrderStatus
{
    public function start($order, $orderItems);

    public function paying($data = array());

    public function paid($data = array());

    public function closed($data = array());

    public function success($data = array());

    public function fail($data = array());

    public function finished($data = array());

}