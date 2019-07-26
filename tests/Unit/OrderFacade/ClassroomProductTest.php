<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\ClassroomProduct;

class ClassroomProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array('buyable' => true)),
            array('functionName' => 'canJoinClassroom', 'returnValue' => array('code' => AccessorInterface::SUCCESS)),
        ));
        $this->assertEquals(null, $classroomProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateOnErrorWhenClassroomUnPurchasable()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array('buyable' => 0)),
            array('functionName' => 'canJoinClassroom', 'returnValue' => array('code' => AccessorInterface::SUCCESS)),
        ));

        $classroomProduct->validate();
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'canJoinCourse', 'returnValue' => array('code' => 'error', 'msg' => 'wrong')),
        ));

        $classroomProduct->validate();
    }
}
