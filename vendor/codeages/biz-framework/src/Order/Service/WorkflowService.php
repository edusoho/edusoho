<?php

namespace Codeages\Biz\Framework\Order\Service;

interface WorkflowService
{
    public function start($order, $orderItems);

    public function close($orderId, $data = array());

    public function paying($orderId, $data = array());

    public function paid($data);

    public function finish($orderId, $data = array());

    public function fail($orderId, $data = array());

    public function refunding($orderId, $data = array());

    public function refunded($orderId, $data = array());


}