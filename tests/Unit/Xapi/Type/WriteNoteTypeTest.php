<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\WriteNoteType;

class WriteNoteTypeTest extends BaseTestCase
{
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
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'search',
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
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'search',
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
            'Course:CourseSetDao',
            array(
                array(
                    'functionName' => 'search',
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
        $taskService->shouldHaveReceived('search');
        $courseService->shouldHaveReceived('search');
        $courseSetService->shouldHaveReceived('search');
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
