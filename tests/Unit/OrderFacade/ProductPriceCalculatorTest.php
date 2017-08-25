<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Command\ProductWrapper\ProductMarketingWrapper;
use Biz\OrderFacade\Product\CourseProduct;

class ProductPriceCalculatorTest extends BaseTestCase
{
    public function testRun()
    {
        $command1 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command1->method('execute')
            ->willReturnCallback(function ($product) {
                $product->price = $product->price - 10;
            });

        $command2 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command2->method('execute')
            ->willReturnCallback(function ($product) {
                $product->price = $product->price - 20;
            });

        $wrapper = new ProductMarketingWrapper();
        $wrapper->setBiz($this->getBiz());

        /* @var $command1 Command */
        $wrapper->addCommand($command1, 1);
        /* @var $command2 Command */
        $wrapper->addCommand($command2, 2);

        $courseProduct = new CourseProduct();
        $courseProduct->price = 100;
        $wrapper->run($courseProduct);

        $this->assertEquals(70, $courseProduct->price);
    }
}
