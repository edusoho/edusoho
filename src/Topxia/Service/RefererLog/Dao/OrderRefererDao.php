<?php
namespace Topxia\Service\RefererLog\Dao;

interface OrderRefererDao
{
    public function getOrderRefererByUv($uv);

    public function getOrderRefererLikeByOrderId($orderId);

    public function createOrderReferer($userRefererOrder);

    public function updateOrderReferer($id, $fields);
}
