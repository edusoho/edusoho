<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\OrderFacadeServiceProvider;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;

class OrderFacadeServiceProviderTest extends BaseTestCase
{
    public function testRegister()
    {
        $biz = $this->getBiz();

        $biz->register(new OrderFacadeServiceProvider());

        $product1 = $biz['order.product.'.CourseProduct::TYPE];
        $product2 = $biz['order.product.'.CourseProduct::TYPE];

        $this->assertNotSame($product1, $product2);
        $this->assertInstanceOf('Biz\OrderFacade\Product\CourseProduct', $biz['order.product.'.CourseProduct::TYPE]);
        $this->assertInstanceOf('Biz\OrderFacade\Product\ClassroomProduct', $biz['order.product.'.ClassroomProduct::TYPE]);

        $this->assertInstanceOf('Biz\OrderFacade\Command\ProductMarketingWrapper', $biz['order.product.marketing_wrapper']);
        $this->assertInstanceOf('Biz\OrderFacade\Command\ProductPriceCalculator', $biz['order.product.price_calculator']);
    }
}
