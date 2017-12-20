<?php

namespace Tests\Unit\OpenCourse\Processor;

use Biz\BaseTestCase;
use Biz\Course\Service\Impl\CourseServiceImpl;
use Biz\OpenCourse\Processor\CourseProcessorFactory;
use Biz\OpenCourse\Service\Impl\OpenCourseServiceImpl;

class CourseProcessorFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $class = CourseProcessorFactory::create('normal');
        $this->assertTrue($class instanceof CourseServiceImpl);

        $openCourseClass = CourseProcessorFactory::create('open');
        $this->assertTrue($openCourseClass instanceof OpenCourseServiceImpl);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage 课程类型不存在
     */
    public function testCreateWithException()
    {
        $class = CourseProcessorFactory::create('none');
    }
}
