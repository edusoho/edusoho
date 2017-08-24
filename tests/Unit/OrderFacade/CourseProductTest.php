<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;

class CourseProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'canJoinCourse', 'returnValue' => array('code' => AccessorInterface::SUCCESS))
        ));

        $courseProduct->validate();
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testValidateWithError()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'canJoinCourse', 'returnValue' => array('code' => 'error', 'msg' => 'wrong'))
        ));

        $courseProduct->validate();
    }

}
