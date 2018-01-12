<?php

namespace Tests\Unit\Accessor;

use Biz\Accessor\AccessorChain;
use Biz\BaseTestCase;
use Biz\Course\Accessor\JoinCourseAccessor;
use Biz\Course\Accessor\JoinCourseMemberAccessor;

class AccessorChainTest extends BaseTestCase
{
    public function testAdd()
    {
        $chain = new AccessorChain();
        $chain->add(new JoinCourseAccessor($this->biz), 100);
        $chain->add(new JoinCourseMemberAccessor($this->biz), 10);

        $this->assertInstanceOf('Biz\Course\Accessor\JoinCourseAccessor', $chain->getAccessor('JoinCourseAccessor'));
        $this->assertInstanceOf('Biz\Course\Accessor\JoinCourseMemberAccessor', $chain->getAccessor('JoinCourseMemberAccessor'));
    }

    public function testProcess()
    {
        $expectedError = array(
            'code' => 'not.found',
            'msg' => 'not found',
        );
        $stub = $this->getMockBuilder('Biz\Course\Accessor\JoinCourseAccessor')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
        $stub->method('process')
            ->willReturn($expectedError);

        $chain = new AccessorChain();
        $chain->add($stub, 100);
    }
}
