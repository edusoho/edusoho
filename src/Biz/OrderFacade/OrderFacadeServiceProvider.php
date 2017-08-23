<?php

namespace Biz\OrderFacade;

use Biz\OrderFacade\Command\ProductMarketingWrapper;
use Biz\OrderFacade\Command\ProductPriceCalculator;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderFacadeServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerProduct($biz);

        $this->registerCommands($biz);
    }

    private function registerProduct(Container $biz)
    {
        $biz[sprintf('order.product.%s', CourseProduct::TYPE)] = $biz->factory(function ($biz) {
            $product = new CourseProduct();
            $product->setBiz($biz);

            return $product;
        });

        $biz[sprintf('order.product.%s', ClassroomProduct::TYPE)] = $biz->factory(function ($biz) {
            $product = new ClassroomProduct();
            $product->setBiz($biz);

            return $product;
        });
    }

    private function registerCommands(Container $biz)
    {
        $productMarketingWrapper = new ProductMarketingWrapper();
        $productMarketingWrapper->setBiz($biz);
        $biz['order.product.marketing_wrapper'] = $productMarketingWrapper;

        $productPriceCalculator = new ProductPriceCalculator();
        $productPriceCalculator->setBiz($biz);
        $biz['order.product.price_calculator'] = $productPriceCalculator;
    }
}
