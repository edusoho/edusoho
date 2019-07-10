<?php

namespace Tests\Unit\Sms\Event;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\Event\TaskEventSubscriber;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;
use Codeages\Biz\Framework\Event\Event;

class TaskEventSubscriberTest extends BaseTestCase
{
    public function testOnTaskUpdate()
    {
        $lessonStartTime = time() + 25 * 60 * 60;
        $task = array(
            'id' => 123,
            'type' => 'live',
            'status' => 'published',
            'startTime' => $lessonStartTime,
            'courseId' => 3,
        );

        $taskEventSubscriber = $this->getTaskEventSubscriber();

        $smsService = $this->mockBiz(
            'Sms:SmsService',
            array(
                array(
                    'functionName' => 'isOpen',
                    'returnValue' => true,
                    'withParams' => array('sms_live_play_one_day'),
                    'times' => 1,
                ),
                array(
                    'functionName' => 'isOpen',
                    'withParams' => array('sms_live_play_one_hour'),
                    'returnValue' => true,
                    'times' => 2,
                ),
            )
        );

        $scheduleService = $this->mockBiz(
            'Scheduler:SchedulerService',
            array(
                array(
                    'functionName' => 'register',
                    'withParams' => array(array(
                        'name' => 'SmsSendOneDayJob_task_123',
                        'expression' => intval($lessonStartTime - 24 * 60 * 60),
                        'class' => 'Biz\Sms\Job\SmsSendOneDayJob',
                        'misfire_threshold' => 60 * 60,
                        'args' => array(
                            'targetType' => 'task',
                            'targetId' => 123,
                        ),
                    )),
                ),
                array(
                    'functionName' => 'register',
                    'withParams' => array(array(
                        'name' => 'SmsSendOneHourJob_task_123',
                        'expression' => intval($lessonStartTime - 60 * 60),
                        'class' => 'Biz\Sms\Job\SmsSendOneHourJob',
                        'misfire_threshold' => 60 * 10,
                        'args' => array(
                            'targetType' => 'task',
                            'targetId' => 123,
                        ),
                    )),
                ),
                array(
                    'functionName' => 'searchJobs',
                    'returnValue' => array(array('id' => 1)),
                ),
                array(
                    'functionName' => 'deleteJob',
                    'returnValue' => array(),
                ),
                array(
                    'functionName' => 'getJobByName',
                    'returnValue' => null,
                ),
            )
        );

        $result = $taskEventSubscriber->onTaskUpdate(new Event($task));
        $smsService->shouldHaveReceived('isOpen')->times(2);
        $scheduleService->shouldHaveReceived('register')->times(2);

        $this->assertEmpty($result);
    }

    public function testOnTaskCreateSync()
    {
        $task = array(
            'id' => 123,
            'status' => 'published',
            'courseId' => 3,
        );

        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByCopyIdAndLockedCourseIds',
                    'returnValue' => array(
                        array(
                            'id' => 124,
                            'type' => 'live',
                            'startTime' => 0,
                        ),
                        array(
                            'id' => 125,
                            'type' => 'testpaper',
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Sms:SmsService',
            array(
                array(
                    'functionName' => 'isOpen',
                    'returnValue' => true,
                ),
            )
        );

        $mockApiClient = \Mockery::mock('Biz\CloudPlatform\Client\CloudAPI');
        $mockApiClient->shouldReceive('post')->andReturn(array());
        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', $mockApiClient);

        $mockSmsProcessor = \Mockery::mock('Biz\Sms\SmsProcessor\TaskSmsProcessor');
        $mockSmsProcessor->shouldReceive('getUrls')->andReturn(array(
            'urls' => array('fake/url'),
            'count' => 1000,
        ));

        ReflectionUtils::setStaticProperty(new SmsProcessorFactory(), 'mockedProcessor', $mockSmsProcessor);

        $taskEventSubscriber = $this->getTaskEventSubscriber();

        $result = $taskEventSubscriber->onTaskCreateSync(new Event($task));

        ReflectionUtils::setStaticProperty(new CloudAPIFactory(), 'api', null);
        ReflectionUtils::setStaticProperty(new SmsProcessorFactory(), 'mockedProcessor', null);

        $this->assertEmpty($result);
    }

    protected function getTaskEventSubscriber()
    {
        return new TaskEventSubscriber($this->getBiz());
    }
}
