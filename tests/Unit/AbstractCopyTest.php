<?php

namespace Tests\Unit;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Monolog\Logger;
use Mockery;

class AbstractCopyTest extends BaseTestCase
{
    public function testCopy()
    {
        $stub = $this->getMockForAbstractClass('Biz\AbstractCopy', array($this->biz, array()));
        $stub->expects($this->any())
                 ->method('preCopy')
                 ->will($this->returnValue(true));
        $stub->expects($this->any())
                 ->method('doCopy')
                 ->will($this->returnValue(true));
        ReflectionUtils::invokeMethod($stub, 'copy', array(array(1), array(2)));
    }

    public function testGetLogger()
    {
        $stub = $this->getMockForAbstractClass('Biz\AbstractCopy', array($this->biz, array()));
        $return = ReflectionUtils::invokeMethod($stub, 'getLogger', array('testLogger'));
        $this->assertTrue($return instanceof Logger);
    }

    public function testGetCurrentNodeName()
    {
        $stub = $this->getMockForAbstractClass('Biz\AbstractCopy', array($this->biz, array()));
        $return = ReflectionUtils::invokeMethod($stub, 'getCurrentNodeName');
        $this->assertTrue(strpos($return, 'mock_abstract') !== false);
    }

    public function testPartsFields()
    {
        $stub = $this->getMockForAbstractClass('Biz\AbstractCopy', array($this->biz, array()));
        $stub->expects($this->any())
                 ->method('getFields')
                 ->will($this->returnValue(array('a', 'b')));
        $return = ReflectionUtils::invokeMethod($stub, 'partsFields', array(array('a' => 1, 'b' => 2, 'c' => 3)));
        $this->assertEquals(array('a' => 1, 'b' => 2), $return);
    }

    public function testProcessChainsDoClone()
    {
        $chains = $this->mockChains();
        $mockObj = Mockery::mock('Biz\Course\Copy\CourseSet\CourseSetCopy');
        $mockObj->shouldReceive('copy')
                         ->withArgs(array(array(1), array()))
                         ->andReturn(true);

        $stub = $this->getMockForAbstractClass('Biz\AbstractCopy', array($this->biz, array()));
        ReflectionUtils::invokeMethod($stub,
            'processChainsDoClone',
            array(
                $chains,
                array(),
                array(),
            )
        );
    }

    public function mockChains()
    {
        return array(
            array(
                'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
                'children' => array(),
            ),
        );
    }
}
