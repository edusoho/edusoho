<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\System\Service\SettingService;
use QiQiuYun\SDK\Auth;
use Biz\Xapi\Type\AskQuestionType;

class AskQuestionTypeTest extends BaseTestCase
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

    public function testPackageWithDiscussType()
    {
        $biz = $this->getBiz();
        $obj = $biz['xapi.push.asked_question'];

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

        $this->mockBiz('Course:ThreadService', array(
            array('functionName' => 'getThread', 'returnValue' => array('id' => 1, 'type' => 'discussion', 'title' => 'thread title', 'content' => 'thread content',  'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1)),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'returnValue' => array('id' => 1, 'type' => 'video', 'activityId' => 1)),
        ));

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'title' => 'test course')),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('id' => 1, 'title' => 'test courseSet', 'subtitle' => 'test subtitle')),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1, 'mediaType' => 'video', 'ext' => array('mediaId' => 0))),
        ));

        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFile', 'returnValue' => array()),
        ));

        $result = $obj->package(array('target_id' => 1, 'uuid' => '23b0ceaa-7948-4f4a-b240-4232a9f4c90a', 'occur_time' => time(), 'user_id' => 1));

        $this->assertEmpty($result);
    }

    public function createXAPIService()
    {
        $settings = $this->getSettingService()->get('storage', array());
        $siteSettings = $this->getSettingService()->get('site', array());
        $xapiSetting = $this->getSettingService()->get('xapi', array());

        $pushUrl = !empty($xapiSetting['push_url']) ? $xapiSetting['push_url'] : 'http://xapi.qiqiuyu.net/vi/';

        $siteName = empty($siteSettings['name']) ? '' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];
        $accessKey = empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'];
        $secretKey = empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'];
        $auth = new Auth($accessKey, $secretKey);

        return new \QiQiuYun\SDK\Service\XAPIService($auth, array(
            'base_uri' => $pushUrl,
            'school' => array(
                'accessKey' => $accessKey,
                'url' => $siteUrl,
                'name' => $siteName,
            ),
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
