<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use Biz\Xapi\Type\AudioListen;

class AudioListenTypeTest extends BaseTestCase
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
                    'functionName' => 'getXapiSdk',
                    'returnValue' => $this->getXapiService()->getXapiSdk(),
                ),
            )
        );

        $this->mockBiz(
            'Xapi:ActivityWatchLogDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        0 => array(
                            'id' => 121,
                            'task_id' => 2223,
                            'course_id' => 2221,
                            'watched_time' => 1231,
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
                        array(
                            'id' => 2223,
                            'activityId' => 2224,
                            'type' => 'video',
                            'title' => 'task_title',
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
                            'id' => 2221,
                            'title' => 'course title',
                            'courseSetId' => 2222,
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
                            'id' => 2222,
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
                            'id' => 2224,
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
                    'returnValue' => array(
                        0 => array(
                            'id' => 333333,
                            'fileId' => 444456,
                        ),
                    ),
                ),
            )
        );

        $activityDao = $this->mockBiz(
            'Activity:ActivityDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'withParams' => array(array(2224)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 2224,
                            'mediaType' => 'video',
                            'title' => 'test activity',
                            'mediaId' => 123,
                        ),
                    ),
                ),
            )
        );

        $videoActivityDao = $this->mockBiz(
            'Activity:VideoActivityDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'withParams' => array(),
                    'returnValue' => array(
                        0 => array(
                            'id' => 123,
                            'mediaType' => 'video',
                            'title' => 'test activity',
                            'mediaId' => 333333,
                        ),
                    ),
                ),
            )
        );

        $type = new AudioListen();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(
            array(
                'target_id' => 121,
                'user_id' => 12121,
                'uuid' => '123123123dse',
                'occur_time' => time(),
            ),
        ));

        $taskService->shouldHaveReceived('search');
        $courseService->shouldHaveReceived('search');
        $courseSetService->shouldHaveReceived('search');
        $uploadFileService->shouldHaveReceived('findFilesByIds');

        $packageInfo = reset($packageInfo);

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://activitystrea.ms/schema/1.0/listen', $packageInfo['verb']['id']);
        $this->assertEquals(2224, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }

    public function testPackageWithEmptyStatements()
    {
        $type = new AudioListen();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array());

        $this->assertEmpty($packageInfo);
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
