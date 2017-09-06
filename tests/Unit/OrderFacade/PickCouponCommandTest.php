<?php

namespace CouponPlugin\Tests;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Deduct\PickCouponCommand;
use Biz\OrderFacade\Product\Product;

class PickCouponCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */
        $product->price = 100;

        $this->mockBiz('Coupon:CouponService', array(
            array('functionName' => 'checkCoupon', 'returnValue' => array('useable' => 'yes', 'afterAmount' => 90)),
            array('functionName' => 'getCouponByCode', 'returnValue' => array('id' => 1, 'type' => 'minus', 'rate' => 10)),
            array('functionName' => 'getDeductAmount', 'returnValue' => 10),
        ));

        $command = new PickCouponCommand();

        $command->setBiz($this->getBiz());

        $command->execute($product, array('couponCode' => '123'));

        $this->assertNotNull($product->pickedDeducts);

        $this->assertEquals(10, $product->pickedDeducts[0]['deduct_amount']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testExecuteWithError()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */

        $this->mockBiz('Coupon:CouponService', array(
            array('functionName' => 'checkCoupon', 'returnValue' => array('useable' => 'no', 'afterAmount' => 90)),
        ));

        $command = new PickCouponCommand();

        $command->setBiz($this->getBiz());

        $command->execute($product, array('couponCode' => '123'));
    }
}
