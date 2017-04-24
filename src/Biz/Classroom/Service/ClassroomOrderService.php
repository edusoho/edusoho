<?php

namespace Biz\Classroom\Service;

interface ClassroomOrderService
{
    public function createOrder($id);

    public function doSuccessPayOrder($id);

    public function applyRefundOrder($id, $amount, $reason, $container);

    public function getOrder($id);
}
