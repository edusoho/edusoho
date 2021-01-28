<?php

namespace Tests\Unit\OpenCourse\Event;

use Biz\BaseTestCase;
use Biz\OpenCourse\Event\OpenCourseThreadEventProcessor;
use Codeages\Biz\Framework\Event\Event;

class OpenCourseThreadEventProcessorTest extends BaseTestCase
{
    public function testOnPostCreate()
    {
        $processor = new OpenCourseThreadEventProcessor($this->biz);
        $event = new Event(array('targetId' => 123));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'waveCourse',
                    'withParams' => array(123, 'postNum', 1),
                ),
            )
        );
        $result = $processor->onPostCreate($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('waveCourse')->times(1);
    }

    public function testOnPostDelete()
    {
        $processor = new OpenCourseThreadEventProcessor($this->biz);
        $event = new Event(array('targetId' => 123), array('deleted' => 3));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'waveCourse',
                    'withParams' => array(123, 'postNum', -3),
                ),
            )
        );
        $result = $processor->onPostDelete($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('waveCourse')->times(1);
    }
}
