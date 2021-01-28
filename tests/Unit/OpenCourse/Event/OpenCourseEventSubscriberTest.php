<?php

namespace Tests\Unit\OpenCourse\Event;

use Biz\BaseTestCase;
use Biz\OpenCourse\Event\OpenCourseEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class OpenCourseEventSubscriberTest extends BaseTestCase
{
    public function testOnMaterialCreate()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(array(
            'lessonId' => 123,
            'source' => 'opencoursematerial',
            'type' => 'openCourse',
        ));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'waveCourseLesson',
                    'withParams' => array(123, 'materialNum', 1),
                ),
            )
        );
        $result = $processor->onMaterialCreate($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('waveCourseLesson')->times(1);
    }

    public function testOnMaterialUpdate()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(
            array(
                'lessonId' => 123,
                'courseId' => 12,
                'source' => 'opencoursematerial',
            ),
            array(
                'argument' => array(),
            )
        );

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getCourseLesson',
                    'withParams' => array(12, 123),
                ),
                array(
                    'functionName' => 'waveCourseLesson',
                    'withParams' => array(123, 'materialNum', 1),
                ),
            )
        );
        $result = $processor->onMaterialUpdate($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('getCourseLesson')->times(1);
        $openCourseService->shouldHaveReceived('waveCourseLesson')->times(1);
    }

    public function testOnMaterialUpdateWithArguments()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(
            array(
                'lessonId' => 0,
                'courseId' => 12,
                'source' => 'opencoursematerial',
                'type' => 'openCourse',
            ),
            array(
                'argument' => array(
                    'lessonId' => 123,
                ),
            )
        );

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getCourseLesson',
                    'withParams' => array(12, 0),
                ),
                array(
                    'functionName' => 'updateLesson',
                    'withParams' => array(12, 123, array('materialNum' => 3)),
                ),
            )
        );

        $materialService = $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'countMaterials',
                    'withParams' => array(array(
                        'courseId' => 12,
                        'lessonId' => 123,
                        'source' => 'opencoursematerial',
                        'type' => 'openCourse',
                    )),
                    'returnValue' => 3,
                ),
            )
        );
        $result = $processor->onMaterialUpdate($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('getCourseLesson')->times(1);
        $openCourseService->shouldHaveReceived('updateLesson')->times(1);
        $materialService->shouldHaveReceived('countMaterials')->times(1);
    }

    public function testOnMaterialDelete()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(array(
            'courseId' => 12,
            'lessonId' => 123,
            'source' => 'opencoursematerial',
            'type' => 'openCourse',
        ));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getCourseLesson',
                    'withParams' => array(12, 123),
                    'returnValue' => array(
                        'id' => 123,
                    ),
                ),
                array(
                    'functionName' => 'waveCourseLesson',
                    'withParams' => array(123, 'materialNum', -1),
                ),
            )
        );
        $result = $processor->onMaterialDelete($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('getCourseLesson')->times(1);
        $openCourseService->shouldHaveReceived('waveCourseLesson')->times(1);
    }

    public function testOnMaterialDeleteWithOpenCourseLessonSource()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(array(
            'courseId' => 12,
            'lessonId' => 123,
            'source' => 'opencourselesson',
            'type' => 'openCourse',
        ));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'getCourseLesson',
                    'withParams' => array(12, 123),
                    'returnValue' => array(
                        'id' => 123,
                    ),
                ),
                array(
                    'functionName' => 'resetLessonMediaId',
                    'withParams' => array(123),
                ),
            )
        );
        $result = $processor->onMaterialDelete($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('getCourseLesson')->times(1);
        $openCourseService->shouldHaveReceived('resetLessonMediaId')->times(1);
    }

    public function testOnLiveReplayGenerate()
    {
        $processor = new OpenCourseEventSubscriber($this->biz);
        $event = new Event(array(array(
            'type' => 'liveOpen',
            'courseId' => 12,
            'lessonId' => 123,
        )));

        $openCourseService = $this->mockBiz(
            'OpenCourse:OpenCourseService',
            array(
                array(
                    'functionName' => 'updateLesson',
                    'withParams' => array(12, 123, array(
                        'replayStatus' => 'generated',
                    )),
                ),
            )
        );
        $result = $processor->onLiveReplayGenerate($event);
        $this->assertNull($result);
        $openCourseService->shouldHaveReceived('updateLesson')->times(1);
    }
}
