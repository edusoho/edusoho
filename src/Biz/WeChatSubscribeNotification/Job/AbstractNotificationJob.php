<?php

namespace Biz\WeChatSubscribeNotification\Job;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AbstractNotificationJob extends AbstractJob
{
    const LIMIT_NUM = 1000;

    public function execute()
    {
    }

    protected function sendNotifications($templateCode, $logName, $list, $batchId = 0)
    {
        $batchs = array_chunk($list, self::LIMIT_NUM);
        foreach ($batchs as $batch) {
            $this->getWeChatService()->sendSubscribeWeChatNotification($templateCode, $logName, $batch, $batchId);
        }

        return true;
    }

    protected function getCourseNameByCourse($course)
    {
        return empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
    }

    protected function plainTextByLength($text, $length)
    {
        if (mb_strlen($text, 'utf-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 1, 'utf-8').'…';
    }

    protected function sendSubscribeSmsNotification($userIds, $smsType, $cloudSmsType, $smsTemplateId, $smsParams)
    {
        if (empty($userIds)) {
            return [];
        }

        if ($this->getWeChatService()->isSubscribeSmsEnabled($smsType) && !$this->getWeChatService()->isSubscribeSmsEnabled($cloudSmsType)) {
            return $this->getWeChatService()->sendSubscribeSms(
                $smsType,
                $userIds,
                $smsTemplateId,
                $smsParams
            );
        }
    }

    protected function getCloudNotificationClient()
    {
        $biz = $this->biz;

        return $biz['ESCloudSdk.notification'];
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz->service('Notification:NotificationService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
