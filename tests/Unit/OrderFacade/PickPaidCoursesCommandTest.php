<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Deduct\PickPaidCoursesCommand;
use Biz\OrderFacade\Product\Product;

class PickPaidCoursesCommandTest extends BaseTestCase
{
    public function testExecute()
    {
        $product = $this->getMockBuilder('Biz\OrderFacade\Product\Product')->getMock();

        /* @var $product Product */
        $product->targetId = 1;
        $product->targetType = 'classroom';
        $product->originPrice = 100;

        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('discount_buy' => 1)),
        ));

        $classroomCourses = array(
            array('id' => 1, 'originPrice' => 10),
            array('id' => 2, 'originPrice' => 20),
        );
        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'findUserPaidCoursesInClassroom', 'returnValue' => array(
                array(), array(array('id' => 1, 'target_id' => 1, 'pay_amount' => 10)),
            )),
        ));

        $command = new PickPaidCoursesCommand();
        $command->setBiz($this->getBiz());
        /* @var $product Product */
        $command->execute($product);

        $this->assertCount(1, $product->pickedDeducts);
        $this->assertArrayHasKey('deduct_amount', $product->pickedDeducts[0]);
        $this->assertArrayHasKey('deduct_id', $product->pickedDeducts[0]);
        $this->assertEquals('paidCourse', $product->pickedDeducts[0]['deduct_type']);
    }
}
