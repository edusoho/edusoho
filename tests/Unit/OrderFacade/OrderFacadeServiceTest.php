<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

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
            array('deduct_id' => 1, 'deduct_type' => 'rewardPoint', 'deduct_amount' => 20),
            array('deduct_id' => 2, 'deduct_type' => 'discount', 'deduct_amount' => 100),
        );

        $order = $this->getOrderFacadeService()->create($courseProduct);

        $this->assertEquals($fakeOrder, $order);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCheckOrderBeforePay()
    {
        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'getOrderBySn', 'returnValue' => array())
        ));

        $this->getOrderFacadeService()->checkOrderBeforePay('12456');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
