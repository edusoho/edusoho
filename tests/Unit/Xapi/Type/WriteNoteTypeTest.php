<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\WriteNoteType;

class WriteNoteTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('site', array()),
                    'returnValue' => array(
                        'siteName' => 'abc',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('xapi', array()),
                    'returnValue' => array(
                        'pushUrl' => '',
                    ),
                ),
            )
        );

        $courseNoteService = $this->mockBiz('Course:CourseNoteService',
            array(
                array(
                    'functionName' => 'getNote',
                    'withParams' => array('1'),
                    'returnValue' => array(
                        'id' => 1,
                        'taskId' => 100,
                        'courseId' => 1,
                        'courseSetId' => 1,
                        'content' => '12345678',
                    ),
                ),
            )
        );

        $taskService = $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'withParams' => array(100),
                    'returnValue' => array(
                        'activityId' => 1000,
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
                    'withParams' => array(1),
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
                    'withParams' => array(1),
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
                    'withParams' => array(1000, true),
                    'returnValue' => array(
                        'id' => 1000,
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

        $type = new WriteNoteType();
        $type->setBiz($this->biz);
        $packageInfo = $type->package(array(
            'target_id' => 1,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        ));

        $courseNoteService->shouldHaveReceived('getNote');
        $taskService->shouldHaveReceived('getTask');
        $courseService->shouldHaveReceived('getCourse');
        $courseSetService->shouldHaveReceived('getCourseSet');
        $activityService->shouldHaveReceived('getActivity');
        $uploadFileService->shouldHaveReceived('getFile');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('https://w3id.org/xapi/adb/verbs/noted', $packageInfo['verb']['id']);
        $this->assertEquals(1000, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }

    public function testPackages()
    {
        $type = new WriteNoteType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array());

        $this->assertEmpty($packageInfo);

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('site', array()),
                    'returnValue' => array(
                        'siteName' => 'abc',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('xapi', array()),
                    'returnValue' => array(
                        'pushUrl' => '',
                    ),
                ),
            )
        );

        $courseNoteService = $this->mockBiz('Course:CourseNoteService',
            array(
                array(
                    'functionName' => 'searchNotes',
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'taskId' => 100,
                            'courseId' => 1,
                            'courseSetId' => 1,
                            'content' => '12345678',
                        ),
                    ),
                ),
            )
        );

        $taskService = $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'findTasksByIds',
                    'withParams' => array(array(100)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 100,
                            'activityId' => 1000,
                            'type' => 'video',
                        ),
                    ),
                ),
            )
        );

        $courseService = $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'findCoursesByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'courseSetId' => 1,
                            'title' => 'course title',
                        ),
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'findCourseSetsByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                        ),
                    ),
                ),
            )
        );

        $activityService = $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'findActivities',
                    'withParams' => array(array(1000), true),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1000,
                            'mediaType' => 'video',
                            'ext' => array(
                                'mediaId' => 333333,
                            ),
                        ),
                    ),
                ),
            )
        );

        $uploadFileService = $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'findFilesByIds',
                    'withParams' => array(array(333333)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 333333,
                            'fileId' => 444456,
                        ),
                    ),
                ),
            )
        );

        $type = new WriteNoteType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(
            0 => array(
                'target_id' => 1,
                'user_id' => 12121,
                'uuid' => '123123123dse',
                'occur_time' => time(),
            ),
        ));

        $packageInfo = reset($packageInfo);

        $courseNoteService->shouldHaveReceived('searchNotes');
        $taskService->shouldHaveReceived('findTasksByIds');
        $courseService->shouldHaveReceived('findCoursesByIds');
        $courseSetService->shouldHaveReceived('findCourseSetsByIds');
        $activityService->shouldHaveReceived('findActivities');
        $uploadFileService->shouldHaveReceived('findFilesByIds');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('https://w3id.org/xapi/adb/verbs/noted', $packageInfo['verb']['id']);
        $this->assertEquals(1000, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }
}
