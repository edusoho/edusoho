<?php

namespace Tests\Unit\WeChatNotification\Event;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Event\WeChatNotificationEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class WeChatNotificationEventSubscriberTest extends BaseTestCase
{
    public function testOnTaskPublish()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockTemplateId('courseUpdate');
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 123, 'status' => 'published', 'courseSetId' => 123],
                'withParams' => [123],
            ],
        ]);
        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'returnValue' => ['id' => 123, 'status' => 'published'],
                'withParams' => [123],
            ],
        ]);
        $subscriber->onTaskPublish($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnTaskUnpublish()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $SchedulerService = $this->mockBiz('Scheduler:SchedulerService', [
            [
                'functionName' => 'searchJobs',
                'returnValue' => [['id' => 11]],
            ],
            [
                'functionName' => 'deleteJob',
            ],
        ]);
        $subscriber->onTaskUnpublish($this->getTaskEvent());
        $SchedulerService->shouldHaveReceived('searchJobs');
        $SchedulerService->shouldHaveReceived('deleteJob');
    }

    public function testOnTaskDelete()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $schedulerService = $this->mockBiz('Scheduler:SchedulerService', [
            [
                'functionName' => 'searchJobs',
                'returnValue' => [['id' => 11]],
            ],
            [
                'functionName' => 'deleteJob',
            ],
        ]);
        $subscriber->onTaskDelete($this->getTaskEvent());
        $schedulerService->shouldHaveReceived('searchJobs');
        $schedulerService->shouldHaveReceived('deleteJob');
    }

    public function testOnTaskUpdate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['liveOpen', 'beforeOneHour'],
            ],
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['liveOpen', 'beforeOneDay'],
            ],
        ]);
        $subscriber->onTaskUpdate($this->getTaskEvent(['type' => 'live', 'startTime' => time() + 87000]));
        $hourJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LiveOneHour_123');
        $dayJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LiveOneDay_123');

        $this->assertNotEmpty($hourJob);
        $this->assertNotEmpty($dayJob);
    }

    public function testOnTaskPublishSync()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockTemplateId('courseUpdate');
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => [['id' => 123]],
                'withParams' => [123, 1],
            ],
        ]);

        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'returnValue' => [['id' => 123, 'type' => 'normal', 'courseId' => 123]],
                'withParams' => [123, [123]],
            ],
        ]);
        $subscriber->onTaskPublishSync($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnTaskCreateSync()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockTemplateId('courseUpdate');
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => [['id' => 123]],
                'withParams' => [123, 1],
            ],
        ]);

        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'returnValue' => [['id' => 123, 'type' => 'normal', 'courseId' => 123]],
                'withParams' => [123, [123]],
            ],
        ]);
        $subscriber->onTaskCreateSync($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnPaid()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['coinRecharge'],
            ],
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['paySuccess'],
            ],
            [
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => ['id' => 2, 'isSubscribe' => 1, 'openId' => 'testOpenId'],
                'withParams' => ['2'],
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);
        $result = $subscriber->onPaid($this->getTradeEvent());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);

        $this->mockBiz('Order:OrderService', [
            [
                'functionName' => 'getOrderBySn',
                'returnValue' => ['id' => 111],
                'withParams' => ['orderSn'],
            ],
            [
                'functionName' => 'findOrderItemsByOrderId',
                'returnValue' => [['target_type' => 'course', 'target_id' => 123]],
                'withParams' => [111],
            ],
        ]);
        $result = $subscriber->onPaid($this->getTradeEvent(['type' => 'pay']));
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);
    }

    public function testOnCourseQuestionCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $return = [['id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1],
            ['id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1], ];
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['askQuestion'],
            ],
            [
                'functionName' => 'searchWeChatUsers',
                'returnValue' => $return,
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);

        $courseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'title' => 'test'],
                'withParams' => ['1'],
            ],
        ]);

        $courseMemberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'findCourseTeachers',
                'returnValue' => [['userId' => 1], ['userId' => 2]],
                'withParams' => ['1'],
            ],
        ]);

        $result = $subscriber->onCourseQuestionCreate($this->getCourseThread());
        $weChatService->shouldHaveReceived('searchWeChatUsers');
        $courseService->shouldHaveReceived('getCourse');
        $courseMemberService->shouldHaveReceived('findCourseTeachers');
        $this->assertNull($result);
    }

    public function testOnClassroomQuestionCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $return = [['id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1],
            ['id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1], ];
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['askQuestion'],
            ],
            [
                'functionName' => 'searchWeChatUsers',
                'returnValue' => $return,
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'returnValue' => ['id' => 1, 'title' => 'test'],
                'withParams' => ['1'],
            ],
            [
                'functionName' => 'findTeachers',
                'returnValue' => ['1', '2'],
                'withParams' => ['1'],
            ],
        ]);

        $result = $subscriber->onClassroomQuestionCreate($this->getThread());
        $weChatService->shouldHaveReceived('searchWeChatUsers');
        $classroomService->shouldHaveReceived('getClassroom');
        $classroomService->shouldHaveReceived('findTeachers');
        $this->assertNull($result);
    }

    public function testOnCourseQuestionAnswerCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['answerQuestion'],
            ],
            [
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => ['id' => 1, 'isSubscribe' => 1, 'openId' => 'testOpenId'],
                'withParams' => ['1'],
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);

        $courseService = $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => ['id' => 1, 'title' => 'test'],
                'withParams' => ['1'],
            ],
        ]);

        $courseThreadService = $this->mockBiz('Course:ThreadService', [
            [
                'functionName' => 'getThread',
                'returnValue' => ['id' => 1, 'createdTime' => time(), 'userId' => 1],
                'withParams' => ['1', '1'],
            ],
        ]);

        $courseMemberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'isCourseTeacher',
                'returnValue' => true,
                'withParams' => ['1', '1'],
            ],
        ]);

        $result = $subscriber->onCourseQuestionAnswerCreate($this->getCoursePost());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $courseMemberService->shouldHaveReceived('isCourseTeacher');
        $courseThreadService->shouldHaveReceived('getThread');
        $courseService->shouldHaveReceived('getCourse');
        $this->assertNull($result);
    }

    public function testOnClassroomQuestionAnswerCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', ['cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey']);
        $weChatService = $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => ['answerQuestion'],
            ],
            [
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => ['id' => 1, 'isSubscribe' => 1, 'openId' => 'testOpenId'],
                'withParams' => ['1'],
            ],
            [
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ],
        ]);

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'returnValue' => ['id' => 1, 'title' => 'test'],
                'withParams' => ['1'],
            ],
            [
                'functionName' => 'isClassroomTeacher',
                'returnValue' => true,
                'withParams' => ['1', '1'],
            ],
        ]);

        $threadService = $this->mockBiz('Thread:ThreadService', [
            [
                'functionName' => 'getThread',
                'returnValue' => ['id' => 1, 'createdTime' => time(), 'userId' => 1],
                'withParams' => ['1'],
            ],
        ]);

        $result = $subscriber->onClassroomQuestionAnswerCreate($this->getPost());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $classroomService->shouldHaveReceived('isClassroomTeacher');
        $classroomService->shouldHaveReceived('getClassroom');
        $threadService->shouldHaveReceived('getThread');
        $this->assertNull($result);
    }

    public function mockTemplateId($key)
    {
        $this->mockBiz('WeChat:WeChatService', [
            [
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => [$key],
            ],
        ]);
    }

    private function getTaskEvent($task = [])
    {
        $default = [
            'id' => 123,
            'courseId' => 123,
            'type' => 'normal',
            'startTime' => 0,
            'status' => 'published',
        ];
        $task = array_merge($default, $task);

        return new Event($task);
    }

    private function getPaperResultEvent($paperResult = [])
    {
        $default = [
            'id' => 123,
            'lessonId' => 123,
            'type' => 'testpaper',
            'score' => 0,
            'passedStatus' => 'good',
            'userId' => 2,
        ];
        $paperResult = array_merge($default, $paperResult);

        return new Event($paperResult);
    }

    private function getTradeEvent($trade = [])
    {
        $default = [
            'id' => 123,
            'type' => 'recharge',
            'trade_sn' => 'tradeSn',
            'amount' => 100,
            'pay_time' => time(),
            'user_id' => 2,
            'order_sn' => 'orderSn',
            'title' => 'title',
        ];
        $trade = array_merge($default, $trade);

        return new Event($trade);
    }

    private function getThread($thread = [])
    {
        $default = [
            'id' => 1,
            'targetType' => 'classroom',
            'targetId' => '1',
            'type' => 'question',
            'createdTime' => time(),
            'userId' => 1,
            'title' => 'title',
        ];
        $thread = array_merge($default, $thread);

        return new Event($thread);
    }

    private function getCourseThread($thread = [])
    {
        $default = [
            'id' => 1,
            'type' => 'question',
            'createdTime' => time(),
            'userId' => 1,
            'title' => 'title',
            'courseId' => 1,
        ];
        $thread = array_merge($default, $thread);

        return new Event($thread);
    }

    private function getPost($post = [])
    {
        $default = [
            'id' => 1,
            'targetType' => 'classroom',
            'targetId' => '1',
            'threadId' => '1',
            'createdTime' => time(),
            'userId' => 1,
            'content' => 'content',
        ];
        $post = array_merge($default, $post);

        return new Event($post);
    }

    private function getCoursePost($post = [])
    {
        $default = [
            'id' => 1,
            'courseId' => '1',
            'createdTime' => time(),
            'userId' => 1,
            'threadId' => 1,
            'content' => 'content',
        ];
        $post = array_merge($default, $post);

        return new Event($post);
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
