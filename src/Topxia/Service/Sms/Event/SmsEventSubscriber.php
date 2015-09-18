<?php
namespace Topxia\Service\Sms\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class SmsEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.check' => 'onTestpaperCheck',
            'order.pay.success' => 'onOrderPaySuccess',
            'course.lesson.publish' => 'onCourseLessonPublish',
            'course.lesson.updateStartTime' => 'onCourseLessonUpdateStartTime',
            'course.lesson.delete' => 'onCourseLessonDelete',
        );
    }

    public function onTestpaperCheck(ServiceEvent $event)
    {
        $parameters = array();
        $smsType = 'sms_testpaper_check';
        if($this->getSmsService()->isOpen($smsType)){
            $testpaperResult = $event->getSubject();
            $testId = $testpaperResult['testId'];
            $testpaper = $this->getTestpaperService()->getTestpaper($testId);
            $target = explode('-', $testpaper['target']);
            if ($target[0] == 'course') {
                $courseId = $target[1];
                $course = $this->getCourseService()->getCourse($courseId);
                $parameters['lesson_title'] = '《'.$testpaperResult['paperName'].'》'.'的试卷';
                $parameters['course_title'] = '《'.$course['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'试卷批阅提醒';
                $userId = $testpaperResult['userId'];
                $user = $this->getUserService()->getUser($userId);
                if (strlen($user['verifiedMobile']) != 0) {
                    $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
                }
            }
        }
    }

    public function onOrderPaySuccess(ServiceEvent $event)
    {
        $order = $event->getSubject();
        $targetType = $event->getArgument('targetType');
        $smsType = 'sms_'.$targetType.'_buy';
        if ($this->getSmsService()->isOpen($smsType)) {
            $userId = $order['userId'];
            $user = $this->getUserService()->getUser($userId);
            $parameters = array();
            $parameters['order_title'] = $order['title'];
            if ($targetType == 'coin') {
                $parameters['totalPrice'] = $order['amount'].'元';
            } else {
                $parameters['totalPrice'] = $order['totalPrice'].'元';
            }
            $description = $parameters['order_title'].'成功回执';
            if (strlen($user['verifiedMobile']) != 0) {
                $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
            }
        }
    }

    public function onCourseLessonPublish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $this->createJob($lesson);
    }

    public function onCourseLessonUpdateStartTime(ServiceEvent $event)
    {
        $lesson = $event->getSubject();

        $jobs = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson',$lesson['id']);

        if ($jobs) {
            $this->deleteJob($jobs);
        }

        $this->createJob($lesson);

    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $jobs = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson',$lesson['id']);
        if ($jobs) {
            $this->deleteJob($jobs);
        }
    }

    protected function createJob($lesson)
    {
        $daySmsType = 'sms_live_play_one_day';
        $hourSmsType = 'sms_live_play_one_hour';
        $dayIsOpen = $this->getSmsService()->isOpen($daySmsType);
        $hourIsOpen = $this->getSmsService()->isOpen($hourSmsType);
        if ($lesson['type'] == 'live' && ($dayIsOpen || $hourIsOpen)) {
            if ($dayIsOpen && $lesson['startTime'] >= (time() + 24*60*60)) {
                $startJob = array(
                'name' => "直播短信一天定时",
                'cycle' => 'once',
                'time' => $lesson['startTime'] - 24*60*60,
                'jobClass' => substr(__NAMESPACE__, 0, -5) . '\Job\\smsSendOneDayJob',
                'targetType' => 'lesson',
                'targetId' => $lesson['id']
                );
                $startJob = $this->getCrontabService()->createJob($startJob);
            }

            if ($hourIsOpen && $lesson['startTime'] >= (time() + 60*60)) {
                $startJob = array(
                'name' => "直播短信一小时定时",
                'cycle' => 'once',
                'time' => $lesson['startTime'] - 60*60,
                'jobClass' => substr(__NAMESPACE__, 0, -5) . '\Job\\smsSendOneHourJob',
                'targetType' => 'lesson',
                'targetId' => $lesson['id']
                );
                $startJob = $this->getCrontabService()->createJob($startJob);
            }     
        }
    }

    protected function deleteJob($jobs)
    {
        foreach ($jobs as $key => $job) {
            $this->getCrontabService()->deleteJob($job['id']);
        }
    }


    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    protected function getSmsService()
    {
        return ServiceKernel::instance()->createService('Sms.SmsService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getCrontabService()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService');
    }

}
