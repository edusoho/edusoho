<?php

namespace Tests\Unit\Xapi;

use Biz\BaseTestCase;
use Biz\Xapi\Service\XapiService;
use Biz\Xapi\Type\AudioListen;

class AudioListenTest extends BaseTestCase
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

        $this->mockBiz(
            'Xapi:XapiService',
            array(
                array(
                    'functionName' => 'getWatchLog',
                    'withParams' => array(121),
                    'returnValue' => array(
                        'task_id' => 2223,
                        'course_id' => 2221,
                        'watched_time' => 1231,
                    ),
                ),
                array(
                    'functionName' => 'getXapiSdk',
                    'returnValue' => $this->getXapiService()->getXapiSdk(),
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
                        'title' => 'task_title',
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
                        'courseSetId' => 2222,
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
                        'id' => 2224,
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

        $type = new AudioListen();
        $type->setBiz($this->biz);
        $packageInfo = $type->package(array(
            'target_id' => 121,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        ));

        $taskService->shouldHaveReceived('getTask');
        $courseService->shouldHaveReceived('getCourse');
        $courseSetService->shouldHaveReceived('getCourseSet');
        $activityService->shouldHaveReceived('getActivity');
        $uploadFileService->shouldHaveReceived('getFile');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://activitystrea.ms/schema/1.0/listen', $packageInfo['verb']['id']);
        $this->assertEquals(2224, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
