<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Currency;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Order\Service\OrderService;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function create(Product $product)
    {
        $product->validate();

        $user = $this->biz['user'];
        /* @var $currency Currency */
        $currency = $this->biz['currency'];
        $orderFields = array(
            'title' => $product->title,
            'user_id' => $user['id'],
            'created_reason' => 1,
            'price_type' => $currency->isoCode,
            'currency_exchange_rate' => $currency->exchangeRate,
        );

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getOrderService()->createOrder($orderFields, $orderItems);

        return $order;
    }

    private function makeOrderItems(Product $product)
    {
        $orderItem = array(
            'target_id' => $product->targetId,
            'target_type' => $product->targetType,
            'price_amount' => $product->price,
            'pay_amount' => $product->getPayablePrice(),
            'title' => $product->title,
        );

        $deducts = array();
        foreach ($product->pickedDeducts as $deduct) {
            $deducts[] = array(
                'deduct_id' => $deduct['id'],
                'deduct_type' => $deduct['deduct_type'],
                'deduct_amount' => $deduct['deduct_amount'],
            );
        }

        if ($deducts) {
            $orderItem['deducts'] = $deducts;
        }

        return array($orderItem);
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
