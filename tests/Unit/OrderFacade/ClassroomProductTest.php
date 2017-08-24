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
            array('functionName' => 'canJoinClassroom', 'returnValue' => array('code' => AccessorInterface::SUCCESS))
        ));

        $courseProduct->validate();
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testValidateWithError()
    {
        $courseProduct = new ClassroomProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'canJoinCourse', 'returnValue' => array('code' => 'error', 'msg' => 'wrong'))
        ));

        $courseProduct->validate();
    }

}
