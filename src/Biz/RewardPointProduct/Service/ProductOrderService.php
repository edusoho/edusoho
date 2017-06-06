<?php

namespace Biz\RewardPointProduct\Service;

interface ProductOrderService
{
    public function createProductOrder($fields);

    public function updateProductOrder($id, $fields);

    public function getProductOrder($id);

    public function countProductOrders(array $conditions);

    public function searchProductOrders(array $conditions, array $orderBys, $start, $limit);

    public function findProductOrdersByUserId($userId);

    public function findProductOrdersByProductId($productId);
}
