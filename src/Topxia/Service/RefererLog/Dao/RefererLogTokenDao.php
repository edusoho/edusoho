<?php
namespace Topxia\Service\RefererLog\Dao;

interface RefererLogTokenDao
{
    public function getOrderRefererByUv($uv);

    public function getOrderRefererLikeByOrderId($orderId);

    public function createOrderReferer($userRefererOrder);

    public function updateToken($id, $fields);
}
