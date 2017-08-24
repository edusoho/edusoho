<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Command\ProductMarketingWrapper;
use Biz\OrderFacade\Command\ProductPriceCalculator;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Order\Service\OrderService;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function show(Product $product)
    {
        $product->validate();

        if ($product->price == 0) {
            return $product;
        }

        $this->getProductMarketingWrapper()->run($product);

        return $product;
    }

    public function getPrice(Product $product)
    {
        $this->getProductPriceCalculator()->run($product);

        return $product->payablePrice;
    }

    public function create(Product $product)
    {
        $product->validate();

        $this->getProductPriceCalculator()->run($product);

        $user = $this->biz['user'];
        $orderFields = array(
            'title' => $product->title,
            'user_id' => $user['id'],
            'created_reason' => 1,
        );

        $orderItems = $this->makeOrderItems($product);

        $order = $this->getOrderService()->createOrder($orderFields, $orderItems);

        return $order;
    }

    private function makeOrderItems(Product $product)
    {
        $orderItem = array(
            'target_id' => $product->id,
            'target_type' => $product->type,
            'price_amount' => $product->price,
            'title' => $product->title,
        );

        $deducts = array();
        foreach ($product->pickedDeducts as $deductType => $deduct) {
            $deducts[] = array(
                'deduct_id' => $deduct['id'],
                'deduct_type' => $deductType,
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

    /**
     * @return ProductPriceCalculator
     */
    private function getProductPriceCalculator()
    {
        return $this->biz['order.product.price_calculator'];
    }

    /**
     * @return ProductMarketingWrapper
     */
    private function getProductMarketingWrapper()
    {
        return $this->biz['order.product.marketing_wrapper'];
    }
}
