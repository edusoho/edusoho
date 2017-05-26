<?php

namespace Biz\Sms\Event;

use Biz\Sms\Job\SmsSendOneDayJob;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SmsEventSubscriber extends EventSubscriber implements EventSubscriberInterface
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

        if ($lesson['type'] == 'liveOpen' && isset($lesson['startTime'])
            && ($this->getSmsService()->isOpen('sms_live_play_one_day') || $this->getSmsService()->isOpen('sms_live_play_one_hour'))
        ) {
            $this->registerJob($lesson);
        }
    }

    public function onLiveOpenCourseLessonUpdate(Event $event)
    {
        $context = $event->getSubject();
        $lesson = $context['lesson'];

        if ($lesson['type'] == 'liveOpen' && isset($lesson['startTime'])
            && $lesson['startTime'] != $lesson['fields']['startTime']
            && ($this->getSmsService()->isOpen('sms_live_play_one_day') || $this->getSmsService()->isOpen('sms_live_play_one_hour'))
        ) {
            $this->deleteJob($lesson);

            if ($lesson['status'] == 'published') {
                $this->registerJob($lesson);
            }
        }
    }

    protected function registerJob($lesson)
    {
        $dayIsOpen = $this->getSmsService()->isOpen('sms_live_play_one_day');
        $hourIsOpen = $this->getSmsService()->isOpen('sms_live_play_one_hour');

        if ($dayIsOpen && $lesson['startTime'] >= (time() + 24 * 60 * 60)) {
            $job = array(
                'name' => 'SmsSendOneDayJob_liveOpenLesson_'.$lesson['id'],
                'expression' => $lesson['startTime'] - 24 * 60 * 60,
                'class' => str_replace('\\', '\\\\', SmsSendOneDayJob::class),
                'args' => array(
                    'targetType' => 'liveOpenLesson',
                    'targetId' => $lesson['id'],
                ),
            );
            $this->getSchedulerService()->register($job);
        }

        if ($hourIsOpen && $lesson['startTime'] >= (time() + 60 * 60)) {
            $job = array(
                'name' => 'SmsSendOneHourJob_liveOpenLesson_'.$lesson['id'],
                'expression' => $lesson['startTime'] - 60 * 60,
                'class' => str_replace('\\', '\\\\', SmsSendOneHourJob::class),
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
        $this->getSchedulerService()->deleteJobByName('SmsSendOneDayJob_liveOpenLesson_'.$lesson['id']);
        $this->getSchedulerService()->deleteJobByName('SmsSendOneHourJob_liveOpenLesson_'.$lesson['id']);
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getSmsService()
    {
        return $this->createService('Sms:SmsService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
