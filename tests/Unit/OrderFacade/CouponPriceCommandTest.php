<?php

namespace CouponPlugin\Tests;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\ProductPrice\CouponPriceCommand;
use Biz\OrderFacade\Product\Product;

class CouponPriceCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */
        $product->pickedDeducts['coupon'] = array(
            'code' => '123',
        );

        $product->payablePrice = 100;

        $this->mockBiz('Coupon:CouponService', array(
            array('functionName' => 'checkCoupon', 'returnValue' => array('useable' => 'yes', 'afterAmount' => 90)),
            array('functionName' => 'getCouponByCode', 'returnValue' => array('id' => 1, 'type' => 'minus', 'rate' => 10)),
        ));

        $command = new CouponPriceCommand();

        $command->setBiz($this->getBiz());

        $command->execute($product);

        $this->assertEquals(90, $product->payablePrice);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testExecuteWithError()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */
        $product->pickedDeducts['coupon'] = array(
            'code' => '123',
        );

        $this->mockBiz('Coupon:CouponService', array(
            array('functionName' => 'checkCoupon', 'returnValue' => array('useable' => 'no', 'afterAmount' => 90)),
        ));

        $command = new CouponPriceCommand();

        $command->setBiz($this->getBiz());

        $command->execute($product);
    }
}
