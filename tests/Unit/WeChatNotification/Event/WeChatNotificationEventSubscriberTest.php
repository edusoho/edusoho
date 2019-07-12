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
        $this->mockTemplateId('normalTaskUpdate');
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
                'withParams' => array('oneHourBeforeLiveOpen'),
            ),
            array(
                'functionName' => 'getTemplateId',
                'returnValue' => 'test',
                'withParams' => array('oneDayBeforeLiveOpen'),
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
        $this->mockTemplateId('normalTaskUpdate');
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
        $this->mockTemplateId('normalTaskUpdate');
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

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
