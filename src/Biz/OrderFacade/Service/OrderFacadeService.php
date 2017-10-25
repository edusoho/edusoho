<?php

namespace Biz\OrderFacade\Service;

use Biz\OrderFacade\Product\Product;

interface OrderFacadeService
{
    public function create(Product $product);

    public function getTradePayCashAmount($order, $coinAmount);

    public function isOrderPaid($orderId);

    public function createSpecialOrder(Product $product, $userId, $params = array());

    /**
     * @param $targetType
     * @param $params
     *
     * @return Product
     */
    public function getOrderProduct($targetType, $params);

    public function getOrderProductByOrderItem($orderItem);

    public function checkOrderBeforePay($sn, $params);

    public function sumOrderItemPayAmount($conditions);
}
