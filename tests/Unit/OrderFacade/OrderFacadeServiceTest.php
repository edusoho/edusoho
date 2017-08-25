<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;

class OrderFacadeServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $fakeOrder = array('id' => 1);
        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'createOrder', 'returnValue' => $fakeOrder),
        ));
        /** @var $courseProduct CourseProduct */
        $courseProduct = $this->getMockBuilder('Biz\OrderFacade\Product\CourseProduct')->getMock();

        $courseProduct->pickedDeducts = array(
            'rewardPoint' => array('id' => 2, 'deduct_amount' => 20),
            'discount' => array('id' => 2, 'deduct_amount' => 100),
        );

        $order = $this->getOrderFacadeService()->create($courseProduct);

        $this->assertEquals($fakeOrder, $order);
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
