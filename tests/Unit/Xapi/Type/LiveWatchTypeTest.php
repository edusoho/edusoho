<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Service\XapiService;
use Biz\Xapi\Type\LiveWatchType;

class LiveWatchTypeTest extends BaseTestCase
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

        $xapiService = $this->mockBiz('Xapi:XapiService',
            array(
                array(
                    'functionName' => 'getWatchLog',
                    'withParams' => array(1),
                    'returnValue' => array(
                        'id' => 1,
                        'target_id' => 1,
                        'task_id' => 2,
                        'course_id' => 3,
                        'watched_time' => 100,
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
                    'withParams' => array(2),
                    'returnValue' => array(
                        'id' => 2,
                        'title' => 'test task',
                        'activityId' => 4,
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
                    'withParams' => array(3),
                    'returnValue' => array(
                        'id' => 3,
                        'title' => 'course title',
                        'courseSetId' => 5,
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'withParams' => array(5),
                    'returnValue' => array(
                        'id' => 5,
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
                    'withParams' => array(4, true),
                    'returnValue' => array(
                        'id' => 4,
                        'mediaType' => 'video',
                        'ext' => array(
                            'mediaId' => 333333,
                        ),
                    ),
                ),
            )
        );

        $type = new LiveWatchType();
        $type->setBiz($this->biz);
        $packageInfo = $type->package(array(
            'target_id' => 1,
            'user_id' => 12121,
            'uuid' => '123123123dse',
            'occur_time' => time(),
        ));

        $xapiService->shouldHaveReceived('getWatchLog');
        $xapiService->shouldHaveReceived('getXapiSdk');
        $taskService->shouldHaveReceived('getTask');
        $courseService->shouldHaveReceived('getCourse');
        $courseSetService->shouldHaveReceived('getCourseSet');
        $activityService->shouldHaveReceived('getActivity');

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/verbs/watched', $packageInfo['verb']['id']);
        $this->assertEquals(4, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }

    public function testPackages()
    {
        $type = new LiveWatchType();
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

        $xapiService = $this->mockBiz('Xapi:XapiService',
            array(
                array(
                    'functionName' => 'findWatchLogsByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'target_id' => 1,
                            'task_id' => 2,
                            'course_id' => 3,
                            'watched_time' => 100,
                        ),
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
                    'functionName' => 'findTasksByIds',
                    'withParams' => array(array(2)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 2,
                            'title' => 'test task',
                            'activityId' => 4,
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
                    'withParams' => array(array(3)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 3,
                            'title' => 'course title',
                            'courseSetId' => 5,
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
                    'withParams' => array(array(5)),
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
                    'withParams' => array(array(4), true),
                    'returnValue' => array(
                        0 => array(
                            'id' => 4,
                            'mediaType' => 'video',
                            'ext' => array(
                                'mediaId' => 333333,
                            ),
                        ),
                    ),
                ),
            )
        );

        $type = new LiveWatchType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(
            array(
                'target_id' => 1,
                'user_id' => 12121,
                'uuid' => '123123123dse',
                'occur_time' => time(),
            ),
        ));

        $xapiService->shouldHaveReceived('findWatchLogsByIds');
        $xapiService->shouldHaveReceived('getXapiSdk');
        $taskService->shouldHaveReceived('findTasksByIds');
        $courseService->shouldHaveReceived('findCoursesByIds');
        $courseSetService->shouldHaveReceived('findCourseSetsByIds');
        $activityService->shouldHaveReceived('findActivities');

        $packageInfo = reset($packageInfo);

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('https://w3id.org/xapi/acrossx/verbs/watched', $packageInfo['verb']['id']);
        $this->assertEquals(4, $packageInfo['object']['id']);
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
