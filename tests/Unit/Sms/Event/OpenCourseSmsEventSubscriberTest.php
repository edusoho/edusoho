<?php

namespace Tests\Unit\Sms\Event;

use Biz\BaseTestCase;
use Biz\Sms\Event\OpenCourseSmsEventSubscriber;
use AppBundle\Common\ReflectionUtils;

class OpenCourseSmsEventSubscriberTest extends BaseTestCase
{
    public function testRegisterJob()
    {
        $lessonStartTime = time() + 25 * 60 * 60;
        $smsService = $this->mockBiz(
            'Sms:SmsService',
            array(
                array(
                    'functionName' => 'isOpen',
                    'withParams' => array('sms_live_play_one_day'),
                    'returnValue' => true,
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
                        'name' => 'SmsSendOneDayJob_liveOpenLesson_123',
                        'expression' => $lessonStartTime - 24 * 60 * 60,
                        'class' => 'Biz\Sms\Job\SmsSendOneDayJob',
                        'misfire_threshold' => 3600,
                        'args' => array(
                            'targetType' => 'liveOpenLesson',
                            'targetId' => 123,
                        ),
                    )),
                ),
                array(
                    'functionName' => 'register',
                    'withParams' => array(array(
                        'name' => 'SmsSendOneHourJob_liveOpenLesson_123',
                        'expression' => $lessonStartTime - 60 * 60,
                        'class' => 'Biz\Sms\Job\SmsSendOneHourJob',
                        'args' => array(
                            'targetType' => 'liveOpenLesson',
                            'targetId' => 123,
                        ),
                    )),
                ),
            )
        );

        $subscriber = new OpenCourseSmsEventSubscriber($this->biz);
        $result = ReflectionUtils::invokeMethod($subscriber, 'registerJob', array(array(
            'startTime' => $lessonStartTime,
            'id' => 123,
        )));
        $smsService->shouldHaveReceived('isOpen')->times(2);
        $scheduleService->shouldHaveReceived('register')->times(2);
        $this->assertNull($result);
    }
}
