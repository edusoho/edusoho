<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;

class OrderFacadeServiceTest extends BaseTestCase
{
    public function testShow()
    {
        /** @var $courseProduct CourseProduct */
        $courseProduct = $this->getMockBuilder('Biz\OrderFacade\Product\CourseProduct')->getMock();
        $this->getOrderFacadeService()->show($courseProduct);
    }

    public function testCreate()
    {
        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'createOrder', 'returnValue' => '')
        ));
        /** @var $courseProduct CourseProduct */
        $courseProduct = $this->getMockBuilder('Biz\OrderFacade\Product\CourseProduct')->getMock();

        $this->getOrderFacadeService()->create($courseProduct);
    }

    public function testGetPrice()
    {
        /** @var $courseProduct CourseProduct */
        $courseProduct = $this->getMockBuilder('Biz\OrderFacade\Product\CourseProduct')->getMock();
        $this->getOrderFacadeService()->getPrice($courseProduct);
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
