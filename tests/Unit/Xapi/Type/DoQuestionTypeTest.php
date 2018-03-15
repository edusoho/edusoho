<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\DoQuestionType;

class DoQuestionTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $type = new DoQuestionType();
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

        $questionMarkerResultService = $this->mockBiz('Marker:QuestionMarkerResultService',
            array(
                array(
                    'functionName' => 'findResultsByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'questionMarkerId' => 1,
                            'taskId' => 1,
                            'status' => 'right',
                        ),
                    ),
                ),
            )
        );

        $questionMarkerService = $this->mockBiz('Marker:QuestionMarkerService',
            array(
                array(
                    'functionName' => 'findQuestionMarkersByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'answer' => array(1),
                            'metas' => array(
                                'choices' => array(
                                    1 => 'A',
                                    2 => 'B',
                                ),
                            ),
                            'stem' => 'test',
                            'type' => 'single_choice',
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
                            'id' => 1,
                            'courseId' => 1,
                            'title' => 'test task',
                            'activityId' => 2,
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
                            'title' => 'course title',
                            'courseSetId' => 5,
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
                            'id' => 5,
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
                    'withParams' => array(array(2), true),
                    'returnValue' => array(
                        0 => array(
                            'id' => 2,
                            'mediaType' => 'video',
                            'title' => 'test activity',
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
                            'fileId' => 444456,
                        ),
                    ),
                ),
            )
        );

        $type = new DoQuestionType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(array(
            'target_id' => 1,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        )));

        $questionMarkerResultService->shouldHaveReceived('findResultsByIds');
        $questionMarkerService->shouldHaveReceived('findQuestionMarkersByIds');
        $taskService->shouldHaveReceived('search');
        $courseService->shouldHaveReceived('search');
        $courseSetService->shouldHaveReceived('search');
        $activityService->shouldHaveReceived('findActivities');
        $uploadFileService->shouldHaveReceived('findFilesByIds');

        $packageInfo = reset($packageInfo);

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://adlnet.gov/expapi/verbs/answered', $packageInfo['verb']['id']);
        $this->assertEquals(1, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }
}
