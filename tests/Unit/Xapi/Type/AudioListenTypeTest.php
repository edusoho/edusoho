<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\System\Service\SettingService;
use Biz\Xapi\Service\XapiService;
use QiQiuYun\SDK\Auth;

class AudioListenTypeTest extends BaseTestCase
{
    public function testPackage()
    {
        $biz = $this->getBiz();
        $obj = $biz['xapi.push.listen_audio'];

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

        $this->mockBiz('Xapi:XapiService', array(
            array('functionName' => 'getWatchLog', 'returnValue' => array('id' => 1, 'title' => 'log title', 'content' => 'log content', 'watched_time' => 100, 'task_id' => 1, 'course_id' => 1, 'courseSetId' => 1)),
            array('functionName' => 'getXapiSdk', 'returnValue' => $this->getXapiService()->getXapiSdk()),
        ));

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'getTask', 'returnValue' => array('id' => 1, 'title' => 'test task', 'type' => 'audio', 'activityId' => 1)),
        ));

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'title' => 'test course', 'courseSetId' => 1)),
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

        $this->assertEquals('23b0ceaa-7948-4f4a-b240-4232a9f4c90a', $result['id']);
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

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }
}
