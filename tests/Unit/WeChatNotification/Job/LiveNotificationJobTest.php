<?php

namespace Tests\Unit\WeChatNotification\Job;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Job\LiveNotificationJob;

class LiveNotificationJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('oneDayBeforeLiveOpen'),
            ),
            array(
                'functionName' => 'findSubscribedUsersByUserIdsAndType',
                'returnValue' => array(array('openId' => 'test')),
                'withParams' => array(array(12), 'official'),
            ),
        ));
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => array('status' => 'published', 'courseId' => 3, 'startTime' => time()),
                'withParams' => array(1),
            ),
        ));
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('status' => 'published', 'courseSetId' => 4, 'id' => 5, 'locked' => 0, 'parentId' => 10),
                'withParams' => array(3),
            ),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('status' => 'published', 'parentId' => 1, 'title' => 'test CourseSet', 'type' => 'live'),
                'withParams' => array(4),
            ),
        ));
        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'searchMembers',
                'returnValue' => array(array('userId' => 12)),
            ),
        ));
        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'searchUsers',
                'returnValue' => array(array('id' => 12)),
            ),
        ));

        $job = new LiveNotificationJob(array(), $this->biz);
        $job->args = array('key' => 'oneDayBeforeLiveOpen', 'taskId' => 1, 'url' => 'www.test.com');
        $result = $job->execute();

        $this->assertEmpty($result);
        $weChatService->shouldHaveReceived('findSubscribedUsersByUserIdsAndType');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
