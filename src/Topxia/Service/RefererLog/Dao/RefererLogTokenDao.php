<?php
namespace Topxia\Service\RefererLog\Dao;

interface RefererLogTokenDao
{
    public function getOrderRefererByUv($uv);

    public function getTokenLikeByOrderId($orderId);

    public function addToken($userRefererOrder);

    public function updateToken($id, $fields);
}
