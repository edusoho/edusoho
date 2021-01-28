<?php

namespace Biz\Sms\Event;

use Biz\Sms\Service\SmsService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OpenCourseSmsEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'open.course.lesson.publish' => 'onLiveOpenCourseLessonCreate',
            'open.course.lesson.update' => 'onLiveOpenCourseLessonUpdate',
            'open.course.lesson.delete' => 'onLiveOpenCourseLessonDelete',
        );
    }

    public function onLiveOpenCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];
        $this->deleteJob($lesson);
    }

    public function onLiveOpenCourseLessonCreate(Event $event)
    {
        $lesson = $event->getSubject();

        if ('liveOpen' == $lesson['type'] && isset($lesson['startTime'])
            && ($this->getSmsService()->isOpen('sms_live_play_one_day') || $this->getSmsService()->isOpen('sms_live_play_one_hour'))
        ) {
            $this->registerJob($lesson);
        }
    }

    public function onLiveOpenCourseLessonUpdate(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if ('liveOpen' == $lesson['type'] && isset($lesson['startTime'])
            && $lesson['fields']['startTime'] != $lesson['startTime']
            && ($this->getSmsService()->isOpen('sms_live_play_one_day') || $this->getSmsService()->isOpen('sms_live_play_one_hour'))
        ) {
            $this->deleteJob($lesson);

            if ('published' == $lesson['status']) {
                $this->registerJob($lesson);
            }
        }
    }

    protected function registerJob($lesson)
    {
        $dayIsOpen = $this->getSmsService()->isOpen('sms_live_play_one_day');
        $hourIsOpen = $this->getSmsService()->isOpen('sms_live_play_one_hour');

        if ($dayIsOpen && $lesson['startTime'] >= (time() + 24 * 60 * 60)) {
            //公开课直播，默认一天前通知，在预定时间后1个小时内有效
            $job = array(
                'name' => 'SmsSendOneDayJob_liveOpenLesson_'.$lesson['id'],
                'expression' => intval($lesson['startTime'] - 24 * 60 * 60),
                'class' => 'Biz\Sms\Job\SmsSendOneDayJob',
                'misfire_threshold' => 60 * 60,
                'args' => array(
                    'targetType' => 'liveOpenLesson',
                    'targetId' => $lesson['id'],
                ),
            );
            $this->getSchedulerService()->register($job);
        }

        if ($hourIsOpen && $lesson['startTime'] >= (time() + 60 * 60)) {
            //公开课直播，默认一个小时前通知，在预定时间后10分钟内有效
            $job = array(
                'name' => 'SmsSendOneHourJob_liveOpenLesson_'.$lesson['id'],
                'expression' => intval($lesson['startTime'] - 60 * 60),
                'class' => 'Biz\Sms\Job\SmsSendOneHourJob',
                'misfire_threshold' => 60 * 10,
                'args' => array(
                    'targetType' => 'liveOpenLesson',
                    'targetId' => $lesson['id'],
                ),
            );
            $this->getSchedulerService()->register($job);
        }
    }

    protected function deleteJob($lesson)
    {
        $this->deleteByJobName('SmsSendOneDayJob_liveOpenLesson_'.$lesson['id']);
        $this->deleteByJobName('SmsSendOneHourJob_liveOpenLesson_'.$lesson['id']);
    }

    private function deleteByJobName($jobName)
    {
        $jobs = $this->getSchedulerService()->searchJobs(array('name' => $jobName), array(), 0, PHP_INT_MAX);

        foreach ($jobs as $job) {
            $this->getSchedulerService()->deleteJob($job['id']);
        }
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return SmsService
     */
    private function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }
}
