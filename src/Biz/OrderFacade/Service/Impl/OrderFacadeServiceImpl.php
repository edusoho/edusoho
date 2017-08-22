<?php

namespace Biz\OrderFacade\Service\Impl;

use Biz\BaseService;
use Biz\OrderFacade\Command\ProductMarketingWrapper;
use Biz\OrderFacade\Command\ProductPriceCalculator;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;

class OrderFacadeServiceImpl extends BaseService implements OrderFacadeService
{
    public function show(Product $product)
    {
        $product->validate();
        $newProduct = $this->getProductMarketingWrapper()->run($product);

        /* 其他业务 */

        return $newProduct;
    }

    public function getPrice(Product $product)
    {
        $price = $this->getProductPriceCalculator()->run($product);

        /* 其他业务 */

        return $price;
    }

    public function create(Product $product)
    {
        $product->validate();

        $price = $this->getPrice($product);
        /** 其他业务 */
        $order = $this->getOrderService()->create(array(), array());

        return $order;
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
