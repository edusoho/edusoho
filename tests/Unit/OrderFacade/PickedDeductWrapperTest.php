<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Command\Deduct\PickedDeductWrapper;
use Biz\OrderFacade\Product\CourseProduct;

class PickedDeductWrapperTest extends BaseTestCase
{
    public function testRun()
    {
        $command1 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command1->method('execute')
            ->willReturnCallback(function ($product) {
                $product->originPrice = $product->originPrice - 10;
            });

        $command2 = $this->getMockBuilder('Biz\OrderFacade\Command\Command')
            ->getMock();
        $command2->method('execute')
            ->willReturnCallback(function ($product) {
                $product->originPrice = $product->originPrice - 20;
            });

        $wrapper = new PickedDeductWrapper();
        $wrapper->setBiz($this->getBiz());

        /* @var $command1 Command */
        $wrapper->addCommand($command1, 1);
        /* @var $command2 Command */
        $wrapper->addCommand($command2, 2);

        $courseProduct = new CourseProduct();
        $courseProduct->originPrice = 100;
        $wrapper->wrapper($courseProduct, array());

        $this->assertEquals(70, $courseProduct->originPrice);
    }
}
