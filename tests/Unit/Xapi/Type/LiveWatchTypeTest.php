<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Service\XapiService;
use Biz\Xapi\Type\LiveWatchType;

class LiveWatchTypeTest extends BaseTestCase
{
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
                    'functionName' => 'getXapiSdk',
                    'returnValue' => $this->getXapiService()->getXapiSdk(),
                ),
            )
        );

        $watchDao = $this->mockBiz('Xapi:ActivityWatchLogDao', array(
            array(
                'functionName' => 'search',
                'returnValue' => array(
                    0 => array(
                        'id' => 1,
                        'target_id' => 1,
                        'task_id' => 2,
                        'course_id' => 3,
                        'watched_time' => 100,
                    ),
                ),
            ), ));

        $taskService = $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'search',
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
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'search',
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

        $watchDao->shouldHaveReceived('search');
        $xapiService->shouldHaveReceived('getXapiSdk');
        $taskService->shouldHaveReceived('search');
        $courseService->shouldHaveReceived('search');
        $courseSetService->shouldHaveReceived('search');
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
