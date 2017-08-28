<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Command\Deduct\AvailableDeductWrapper;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;

class AvailableDeductWrapperTest extends BaseTestCase
{
    public function testRun()
    {
        $command1 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command1->method('execute')
            ->willReturnCallback(function ($product) {
                $product->availableDeducts[] = array('discount' => 1);
            });

        $command2 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command2->method('execute')
            ->willReturnCallback(function ($product) {
                $product->availableDeducts[] = array('coupon' => 2);
            });

        $wrapper = new AvailableDeductWrapper();
        $wrapper->setBiz($this->getBiz());

        /* @var $command1 Command */
        $wrapper->addCommand($command1, 1);
        /* @var $command2 Command */
        $wrapper->addCommand($command2, 2);

        $courseProduct = new CourseProduct();
        $wrapper->wrapper($courseProduct);

        $expected = array(
            array('coupon' => 2),
            array('discount' => 1),
        );

        $this->assertEquals($expected, $courseProduct->availableDeducts);
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
