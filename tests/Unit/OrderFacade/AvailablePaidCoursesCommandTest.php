<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Command\Deduct\AvailablePaidCoursesCommand;
use Biz\OrderFacade\Product\Product;

class AvailablePaidCoursesCommandTest extends BaseTestCase
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
        );
        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'findUserPaidCoursesInClassroom', 'returnValue' => array(
                array(1 => array('id' => 1, 'originPrice' => 10)), array(array('id' => 1, 'target_id' => 1, 'pay_amount' => 10)),
            )),
        ));

        $command = new AvailablePaidCoursesCommand();
        $command->setBiz($this->getBiz());
        /* @var $product Product */
        $command->execute($product);

        $this->assertArraySubset($classroomCourses, $product->availableDeducts['paidCourses']);
    }
}
