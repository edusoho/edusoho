<?php

namespace Biz\OrderFacade\Service;

use Biz\OrderFacade\Product\Product;
use Biz\System\Annotation\Log;

interface OrderFacadeService
{
    const DEDUCT_TYPE_ADJUST = 'adjust_price';

    public function create(Product $product);

    public function getTradePayCashAmount($order, $coinAmount);

    public function getRefundDays();

    public function isOrderPaid($orderId);

    public function createSpecialOrder(Product $product, $userId, $params = array(), $type = 'OrderFacade');

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

    /**
     * @param $orderId
     * @param $newPayAmount
     *
     * @return mixed
     * @Log(module="order",action="adjust_price",serviceName="Order:OrderService",funcName="getOrder",param="orderId")
     */
    public function adjustOrderPrice($orderId, $newPayAmount);

    public function getOrderAdjustInfo($order);

    public function addDealer($dealer);
}
