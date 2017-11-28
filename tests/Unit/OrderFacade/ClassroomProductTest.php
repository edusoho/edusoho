<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\ClassroomProduct;

class ClassroomProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $courseProduct = new ClassroomProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array('buyable' => true)),
            array('functionName' => 'canJoinClassroom', 'returnValue' => array('code' => AccessorInterface::SUCCESS)),
        ));
        $this->assertEquals(null, $courseProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException;
     */
    public function testValidateOnErrorWhenClassroomUnPurchasable()
    {
        $courseProduct = new ClassroomProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array('buyable' => true)),
            array('functionName' => 'canJoinClassroom', 'returnValue' => array('code' => AccessorInterface::SUCCESS)),
        ));
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $courseProduct = new ClassroomProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'canJoinCourse', 'returnValue' => array('code' => 'error', 'msg' => 'wrong')),
        ));

        $courseProduct->validate();
    }
}
