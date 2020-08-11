<?php

namespace Tests\Unit\WeChatNotification\Job;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Job\LiveNotificationJob;

class LiveNotificationJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['liveOpen'],
            ],
            [
                'functionName' => 'findSubscribedUsersByUserIdsAndType',
                'returnValue' => [['openId' => 'test']],
                'withParams' => [[12], 'official'],
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'getTask',
                'returnValue' => ['status' => 'published', 'courseId' => 3, 'title' => 'test', 'startTime' => time()],
                'withParams' => [1],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['status' => 'published', 'courseSetId' => 4, 'id' => 5, 'locked' => 0, 'parentId' => 10],
                'withParams' => [3],
            ],
        ]);
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'returnValue' => ['status' => 'published', 'parentId' => 1, 'title' => 'test CourseSet', 'type' => 'live'],
                'withParams' => [4],
            ],
        ]);
        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'searchMembers',
                'returnValue' => [['userId' => 12]],
            ],
        ]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'findUnLockedUsersByUserIds',
                'returnValue' => [['id' => 12]],
            ],
        ]);

        $job = new LiveNotificationJob([], $this->biz);
        $job->args = ['key' => 'liveOpen', 'taskId' => 1, 'url' => 'www.test.com'];
        $result = $job->execute();

        $this->assertEmpty($result);
        $weChatService->shouldHaveReceived('findSubscribedUsersByUserIdsAndType');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
