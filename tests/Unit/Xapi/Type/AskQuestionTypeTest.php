<?php

namespace Tests\Unit\Xapi;

use Biz\BaseTestCase;
use Biz\Xapi\Type\AskQuestionType;

class AskQuestionTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $threadService = $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'withParams' => array(0, 121),
                    'returnValue' => array(
                        'id' => 6758221,
                        'courseId' => 2221,
                        'courseSetId' => 2222,
                        'type' => 'question',
                        'taskId' => 2223,
                        'title' => 'thread title',
                        'content' => 'trhead content',
                    ),
                ),
            )
        );

        $taskService = $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'withParams' => array(2223),
                    'returnValue' => array(
                        'activityId' => 2224,
                        'type' => 'video',
                    ),
                ),
            )
        );

        $courseService = $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(2221),
                    'returnValue' => array(
                        'title' => 'course title',
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'withParams' => array(2222),
                    'returnValue' => array(
                        'title' => 'course set title',
                        'subtitle' => 'course set subtitle',
                    ),
                ),
            )
        );

        $activityService = $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'getActivity',
                    'withParams' => array(2224, true),
                    'returnValue' => array(
                        'mediaType' => 'video',
                        'ext' => array(
                            'mediaId' => 333333,
                        ),
                    ),
                ),
            )
        );

        $uploadFileService = $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'getFile',
                    'withParams' => array(333333),
                    'returnValue' => array(
                        'fileId' => 444456,
                    ),
                ),
            )
        );

        $type = new AskQuestionType();
        $type->setBiz($this->biz);
        $packageInfo = $type->package(array(
            'target_id' => 121,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        ));

        $threadService->shouldHaveReceived('getThread');
        $taskService->shouldHaveReceived('getTask');
        $courseService->shouldHaveReceived('getCourse');
        $courseSetService->shouldHaveReceived('getCourseSet');
        $activityService->shouldHaveReceived('getActivity');
        $uploadFileService->shouldHaveReceived('getFile');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://adlnet.gov/expapi/verbs/asked', $packageInfo['verb']['id']);
        $this->assertEquals(6758221, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }
}
