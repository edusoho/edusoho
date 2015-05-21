<?php

namespace Classroom\Service\Classroom;

interface ClassroomOrderService
{
    public function createOrder($id);

    public function doSuccessPayOrder($id);

    public function applyRefundOrder($id, $amount, $reason, $container);

    public function getOrder($id);
}
