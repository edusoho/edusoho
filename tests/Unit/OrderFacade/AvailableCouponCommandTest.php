<?php

namespace CouponPlugin\Tests;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Deduct\AvailableCouponCommand;
use Biz\OrderFacade\Product\Product;

class AvailableCouponCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */
        $product->targetId = 1;
        $product->targetType = 'course';
        $product->originPrice = 100;

        $coupons = array(
            array('type' => 'minus', 'rate' => 30, 'deadline' => time() - 100),
            array('type' => 'discount', 'rate' => 8, 'deadline' => time()),
        );

        $cardService = $this->getMockBuilder('Biz\Card\Service\CardService')->getMock();
        $cardService->method('findCurrentUserAvailableCouponForTargetTypeAndTargetId')->willReturn($coupons);
        $biz = $this->getBiz();
        $biz['@Card:CardService'] = $cardService;

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('courseSetId' => 1)),
        ));

        $command = new AvailableCouponCommand();
        $command->setBiz($this->getBiz());
        /* @var $product Product */
        $command->execute($product);
        $this->assertArrayHasKey('type', $product->availableDeducts['coupon'][0]);
        $this->assertEquals(30, $product->availableDeducts['coupon'][0]['deduct_amount']);
    }
}
