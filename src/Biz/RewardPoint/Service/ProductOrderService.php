<?php

namespace Biz\RewardPoint\Service;

interface ProductOrderService
{
    public function createProductOrder($fields);

    public function deleteProductOrder($id);

    public function deliverProduct($id, $fields);

    public function updateProductOrder($id, $fields);

    public function getProductOrder($id);

    public function countProductOrders(array $conditions);

    public function searchProductOrders(array $conditions, array $orderBys, $start, $limit);

    public function findProductOrdersByUserId($userId);

    public function findProductOrdersByProductId($productId);

    public function exchangeProduct($order);
}
