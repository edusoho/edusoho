<?php

namespace Tests\Unit\OpenCourse\Processor;

use Biz\BaseTestCase;
use Biz\OpenCourse\Processor\CourseProcessorFactory;

class CourseProcessorFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $class = CourseProcessorFactory::create('normal');
        $this->assertInstanceOf('Biz\Course\Service\Impl\CourseServiceImpl', $class);

        $openCourseClass = CourseProcessorFactory::create('open');
        $this->assertInstanceOf('Biz\OpenCourse\Service\Impl\OpenCourseServiceImpl', $openCourseClass);
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
