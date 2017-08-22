<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Command\ProductMarketingWrapper;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;

class ProductMarketingWrapperTest extends BaseTestCase
{
    public function testRun()
    {
        $command1 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command1->method('execute')
            ->willReturnCallback(function ($product) {
                $product->marketing[] = array('discount' => 1);
            });

        $command2 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command2->method('execute')
            ->willReturnCallback(function ($product) {
                $product->marketing[] = array('coupon' => 2);
            });

        $wrapper = new ProductMarketingWrapper();
        $wrapper->setBiz($this->getBiz());

        /* @var $command1 Command */
        $wrapper->addCommand($command1, 1);
        /* @var $command2 Command */
        $wrapper->addCommand($command2, 2);

        $courseProduct = new CourseProduct();
        $wrapper->run($courseProduct);

        $expected = array(
            array('coupon' => 2),
            array('discount' => 1),
        );

        $this->assertEquals($expected, $courseProduct->marketing);
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
