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
        );
    }

    public function onTestpaperCheck(ServiceEvent $event)
    {
        $smsType = 'sms_testpaper_check';
        if($this->getSmsService()->isOpen($smsType)){
            $testpaperResult = $event->getSubject();
            $userId = $testpaperResult['userId'];
            $user = $this->getUserService()->getUser($userId);
            if ((isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) != 0))) {
                $this->getSmsService()->smsSend($smsType, array($userId));
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
            if ((isset($user['verifiedMobile']) && (strlen($user['verifiedMobile']) != 0))) {
                $parameters['order_title'] = $order['title'];
                $parameters['payment'] = ($order['payment'] == 'wxpay'?'微信支付':'支付宝');
                $parameters['amount'] = $order['amount'];
                $this->getSmsService()->smsSend($smsType, array($userId), $parameters);
            }
        }
    }

    public function onCourseLessonPublish(ServiceEvent $event)
    {
        $daySmsType = 'sms_live_play_one_day';
        $hourSmsType = 'sms_live_play_one_hour';
        $lesson = $event->getSubject();
        $dayIsOpen = $this->getSmsService()->isOpen($daySmsType);
        $hourIsOpen = $this->getSmsService()->isOpen($hourSmsType);
        if ( $lesson['type'] == 'live' && ($dayIsOpen || $hourIsOpen) ) {
            $parameters = array();
            $parameters['lesson_title'] = $lesson['title'];
            $students = $this->getCourseService()->findCourseStudentsByCourseIds(array($lesson['courseId']));
            if (!empty($students)) {
                $studentIds = ArrayToolkit::column($students, 'userId');
                $users = $this->getUserService()->findUsersByIds($studentIds);
                $to = '';
                foreach ($users as $key => $value ) {
                    if (empty($value['verifiedMobile'])) {
                        unset($users[$key]);
                    }
                }
                if (!empty($users)) {
                    $userId = ArrayToolkit::column($users, 'userId');
                }

                if ($dayIsOpen) {
                    $parameters['startTime'] = $lesson['startTime'] - 24 * 3600;
                    $this->getSmsService()->smsSend($dayIsOpen, $userId, $parameters);
                }

                if ($hourIsOpen) {
                    $parameters['startTime'] = $lesson['startTime'] - 3600;
                    $this->getSmsService()->smsSend($hourIsOpen, $userId, $parameters);
                }
            }
        }
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSmsService()
    {
        return ServiceKernel::instance()->createService('Sms.SmsService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

}
