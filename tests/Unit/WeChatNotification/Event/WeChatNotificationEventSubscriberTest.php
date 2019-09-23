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
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 123, 'status' => 'published', 'courseSetId' => 123),
                'withParams' => array(123),
            ),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('id' => 123, 'status' => 'published'),
                'withParams' => array(123),
            ),
        ));
        $subscriber->onTaskPublish($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnTaskUnpublish()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $SchedulerService = $this->mockBiz('Scheduler:SchedulerService', array(
            array(
                'functionName' => 'searchJobs',
                'returnValue' => array(array('id' => 11)),
            ),
            array(
                'functionName' => 'deleteJob',
            ),
        ));
        $subscriber->onTaskUnpublish($this->getTaskEvent());
        $SchedulerService->shouldHaveReceived('searchJobs');
        $SchedulerService->shouldHaveReceived('deleteJob');
    }

    public function testOnTaskDelete()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $schedulerService = $this->mockBiz('Scheduler:SchedulerService', array(
            array(
                'functionName' => 'searchJobs',
                'returnValue' => array(array('id' => 11)),
            ),
            array(
                'functionName' => 'deleteJob',
            ),
        ));
        $subscriber->onTaskDelete($this->getTaskEvent());
        $schedulerService->shouldHaveReceived('searchJobs');
        $schedulerService->shouldHaveReceived('deleteJob');
    }

    public function testOnTaskUpdate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('liveOpen', 'beforeOneHour'),
            ),
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('liveOpen', 'beforeOneDay'),
            ),
        ));
        $subscriber->onTaskUpdate($this->getTaskEvent(array('type' => 'live', 'startTime' => time() + 87000)));
        $hourJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LiveOneHour_123');
        $dayJob = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LiveOneDay_123');

        $this->assertNotEmpty($hourJob);
        $this->assertNotEmpty($dayJob);
    }

    public function testOnTaskPublishSync()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockTemplateId('courseUpdate');
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => array(array('id' => 123)),
                'withParams' => array(123, 1),
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'returnValue' => array(array('id' => 123, 'type' => 'normal', 'courseId' => 123)),
                'withParams' => array(123, array(123)),
            ),
        ));
        $subscriber->onTaskPublishSync($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnTaskCreateSync()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockTemplateId('courseUpdate');
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'findCoursesByParentIdAndLocked',
                'returnValue' => array(array('id' => 123)),
                'withParams' => array(123, 1),
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'findByCopyIdAndLockedCourseIds',
                'returnValue' => array(array('id' => 123, 'type' => 'normal', 'courseId' => 123)),
                'withParams' => array(123, array(123)),
            ),
        ));
        $subscriber->onTaskCreateSync($this->getTaskEvent());
        $job = $this->getSchedulerService()->getJobByName('WeChatNotificationJob_LessonPublish_123');
        $this->assertNotEmpty($job);
    }

    public function testOnTestpaperReviewd()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 123, 'fromCourseId' => 123),
                'withParams' => array(123),
            ),
        ));
        $result = $subscriber->onTestpaperReviewd($this->getPaperResultEvent());
        $this->assertNull($result);

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTaskByCourseIdAndActivityId',
                'returnValue' => array('id' => 123, 'title' => 'testTask', 'courseId' => 123),
                'withParams' => array(123, 123),
            ),
        ));
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('examResult'),
            ),
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('homeworkResult'),
            ),
            array(
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => array('id' => 2, 'isSubscribe' => 1, 'openId' => 'testOpenId'),
                'withParams' => array('2'),
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));
        $result = $subscriber->onTestpaperReviewd($this->getPaperResultEvent());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 123, 'courseSetTitle' => 'title'),
                'withParams' => array(123),
            ),
        ));
        $result = $subscriber->onTestpaperReviewd($this->getPaperResultEvent(array('type' => 'homework')));
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);
    }

    public function testOnPaid()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('coinRecharge'),
            ),
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('paySuccess'),
            ),
            array(
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => array('id' => 2, 'isSubscribe' => 1, 'openId' => 'testOpenId'),
                'withParams' => array('2'),
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));
        $result = $subscriber->onPaid($this->getTradeEvent());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);

        $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'getOrderBySn',
                'returnValue' => array('id' => 111),
                'withParams' => array('orderSn'),
            ),
            array(
                'functionName' => 'findOrderItemsByOrderId',
                'returnValue' => array(array('target_type' => 'course', 'target_id' => 123)),
                'withParams' => array(111),
            ),
        ));
        $result = $subscriber->onPaid($this->getTradeEvent(array('type' => 'pay')));
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $this->assertNull($result);
    }

    public function testOnCourseQuestionCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $return = array(array('id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1),
            array('id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1), );
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('askQuestion'),
            ),
            array(
                'functionName' => 'searchWeChatUsers',
                'returnValue' => $return,
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));

        $courseService = $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'test'),
                'withParams' => array('1'),
            ),
        ));

        $courseMemberService = $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(array('userId' => 1), array('userId' => 2)),
                'withParams' => array('1'),
            ),
        ));

        $result = $subscriber->onCourseQuestionCreate($this->getCourseThread());
        $weChatService->shouldHaveReceived('searchWeChatUsers');
        $courseService->shouldHaveReceived('getCourse');
        $courseMemberService->shouldHaveReceived('findCourseTeachers');
        $this->assertNull($result);
    }

    public function testOnClassroomQuestionCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $return = array(array('id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1),
            array('id' => 1, 'openId' => 'testOpenId', 'unionId' => 4, 'userId' => 1), );
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('askQuestion'),
            ),
            array(
                'functionName' => 'searchWeChatUsers',
                'returnValue' => $return,
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));

        $classroomService = $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'title' => 'test'),
                'withParams' => array('1'),
            ),
            array(
                'functionName' => 'findTeachers',
                'returnValue' => array('1', '2'),
                'withParams' => array('1'),
            ),
        ));

        $result = $subscriber->onClassroomQuestionCreate($this->getThread());
        $weChatService->shouldHaveReceived('searchWeChatUsers');
        $classroomService->shouldHaveReceived('getClassroom');
        $classroomService->shouldHaveReceived('findTeachers');
        $this->assertNull($result);
    }

    public function testOnCourseQuestionAnswerCreate()
    {
        $subscriber = new WeChatNotificationEventSubscriber($this->biz);
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('answerQuestion'),
            ),
            array(
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => array('id' => 1, 'isSubscribe' => 1, 'openId' => 'testOpenId'),
                'withParams' => array('1'),
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));

        $courseService = $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'test'),
                'withParams' => array('1'),
            ),
        ));

        $courseThreadService = $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'createdTime' => time(), 'userId' => 1),
                'withParams' => array('1', '1'),
            ),
        ));

        $courseMemberService = $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'isCourseTeacher',
                'returnValue' => true,
                'withParams' => array('1', '1'),
            ),
        ));

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
        $this->getSettingService()->set('storage', array('cloud_access_key' => 'accessKey', 'cloud_secret_key' => 'secretKey'));
        $weChatService = $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('answerQuestion'),
            ),
            array(
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => array('id' => 1, 'isSubscribe' => 1, 'openId' => 'testOpenId'),
                'withParams' => array('1'),
            ),
            array(
                'functionName' => 'getWeChatSendChannel',
                'returnValue' => 'wechat',
            ),
        ));

        $classroomService = $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'title' => 'test'),
                'withParams' => array('1'),
            ),
            array(
                'functionName' => 'isClassroomTeacher',
                'returnValue' => true,
                'withParams' => array('1', '1'),
            ),
        ));

        $threadService = $this->mockBiz('Thread:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'createdTime' => time(), 'userId' => 1),
                'withParams' => array('1'),
            ),
        ));

        $result = $subscriber->onClassroomQuestionAnswerCreate($this->getPost());
        $weChatService->shouldHaveReceived('getOfficialWeChatUserByUserId');
        $classroomService->shouldHaveReceived('isClassroomTeacher');
        $classroomService->shouldHaveReceived('getClassroom');
        $threadService->shouldHaveReceived('getThread');
        $this->assertNull($result);
    }

    public function mockTemplateId($key)
    {
        $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array($key),
            ),
        ));
    }

    private function getTaskEvent($task = array())
    {
        $default = array(
            'id' => 123,
            'courseId' => 123,
            'type' => 'normal',
            'startTime' => 0,
            'status' => 'published',
        );
        $task = array_merge($default, $task);

        return new Event($task);
    }

    private function getPaperResultEvent($paperResult = array())
    {
        $default = array(
            'id' => 123,
            'lessonId' => 123,
            'type' => 'testpaper',
            'score' => 0,
            'passedStatus' => 'good',
            'userId' => 2,
        );
        $paperResult = array_merge($default, $paperResult);

        return new Event($paperResult);
    }

    private function getTradeEvent($trade = array())
    {
        $default = array(
            'id' => 123,
            'type' => 'recharge',
            'trade_sn' => 'tradeSn',
            'amount' => 100,
            'pay_time' => time(),
            'user_id' => 2,
            'order_sn' => 'orderSn',
            'title' => 'title',
        );
        $trade = array_merge($default, $trade);

        return new Event($trade);
    }

    private function getThread($thread = array())
    {
        $default = array(
            'id' => 1,
            'targetType' => 'classroom',
            'targetId' => '1',
            'type' => 'question',
            'createdTime' => time(),
            'userId' => 1,
            'title' => 'title',
        );
        $thread = array_merge($default, $thread);

        return new Event($thread);
    }

    private function getCourseThread($thread = array())
    {
        $default = array(
            'id' => 1,
            'type' => 'question',
            'createdTime' => time(),
            'userId' => 1,
            'title' => 'title',
            'courseId' => 1,
        );
        $thread = array_merge($default, $thread);

        return new Event($thread);
    }

    private function getPost($post = array())
    {
        $default = array(
            'id' => 1,
            'targetType' => 'classroom',
            'targetId' => '1',
            'threadId' => '1',
            'createdTime' => time(),
            'userId' => 1,
            'content' => 'content',
        );
        $post = array_merge($default, $post);

        return new Event($post);
    }

    private function getCoursePost($post = array())
    {
        $default = array(
            'id' => 1,
            'courseId' => '1',
            'createdTime' => time(),
            'userId' => 1,
            'threadId' => 1,
            'content' => 'content',
        );
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
