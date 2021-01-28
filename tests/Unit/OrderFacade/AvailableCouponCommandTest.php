<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\Card\Service\CardService;
use Biz\OrderFacade\Command\Deduct\AvailableCouponCommand;
use Biz\OrderFacade\Product\Product;

class AvailableCouponCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder(Product::class)->getMock();

        /* @var $product Product */
        $product->targetId = 1;
        $product->targetType = 'course';
        $product->originPrice = 100;
        $product->originalTargetId = 1;

        $coupons = [
            ['type' => 'minus', 'targetType' => 'course', 'targetId' => 1, 'createdTime' => time() - 100, 'rate' => 30, 'deadline' => time() - 100],
            ['type' => 'discount', 'targetType' => 'course', 'targetId' => 1, 'createdTime' => time(), 'rate' => 8, 'deadline' => time()],
        ];

        $cardService = $this->getMockBuilder(CardService::class)->getMock();
        $cardService->method('findCurrentUserAvailableCouponForTargetTypeAndTargetId')->willReturn($coupons);
        $biz = $this->getBiz();
        $biz['@Card:CardService'] = $cardService;

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['courseSetId' => 1]],
        ]);

        $command = new AvailableCouponCommand();
        $command->setBiz($this->getBiz());
        /* @var $product Product */
        $command->execute($product);
        $this->assertArrayHasKey('type', $product->availableDeducts['coupon'][0]);
        $this->assertEquals(30, $product->availableDeducts['coupon'][0]['deduct_amount']);
    }
}
