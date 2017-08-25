<?php

namespace CouponPlugin\Tests;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\ProductAvailableCouponCommand;
use Biz\OrderFacade\Product\Product;

class ProductAvailableCouponCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        $product->id = 1;
        $product->type = 'course';
        $product->price = 100;

        $coupons = array(
            array('type' => 'minus', 'rate' => 30),
            array('type' => 'discount', 'rate' => 8)
        );

        $cardService = $this->getMockBuilder('Biz\Card\Service\CardService')->getMock();
        $cardService->method('findCurrentUserAvailableCouponForTargetTypeAndTargetId')->willReturn($coupons);
        $cardService->method('sortArrayByField')->willReturnArgument(0);
        $biz = $this->getBiz();
        $biz['@Card:CardService'] = $cardService;

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('courseSetId' => 1))
        ));

        $command = new ProductAvailableCouponCommand();
        $command->setBiz($this->getBiz());
        /** @var $product Product */
        $command->execute($product);

        $this->assertArrayHasKey('type', $product->availableDeducts['coupon'][0]);
        $this->assertEquals(30, $product->availableDeducts['coupon'][0]['deduct_amount']);
    }
}