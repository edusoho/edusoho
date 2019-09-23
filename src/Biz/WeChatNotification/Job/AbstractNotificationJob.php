<?php

namespace Biz\WeChatNotification\Job;

use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Notification\Service\NotificationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\System\Service\LogService;
use Biz\WeChat\Service\WeChatService;
use Biz\Course\Service\CourseService;
use Biz\AppLoggerConstant;
use AppBundle\Common\ArrayToolkit;

class AbstractNotificationJob extends AbstractJob
{
    const OFFICIAL_TYPE = 'official';

    const LIMIT_NUM = 1000;

    public function execute()
    {
    }

    protected function sendNotifications($key, $logName, $userIds, $templateData)
    {
        if (empty($userIds)) {
            return;
        }

        $wechatSetting = $this->getSettingService()->get('wechat', array());
        $channel = $this->getWeChatService()->getWeChatSendChannel();

        $users = $this->getUserService()->searchUsers(
            array('userIds' => $userIds, 'locked' => 0),
            array(),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($users, 'id');
        if (empty($userIds)) {
            return;
        }
        $subscribedUsers = $this->getWeChatService()->findSubscribedUsersByUserIdsAndType($userIds, self::OFFICIAL_TYPE);
        $subscribedUsers = ArrayToolkit::index($subscribedUsers, 'userId');
        $batchs = array_chunk($subscribedUsers, self::LIMIT_NUM);
        foreach ($batchs as $batch) {
            $list = array();
            foreach ($batch as $user) {
                $data = isset($templateData[$user['userId']]) ? $templateData[$user['userId']] : $templateData[0];
                if (!is_array($data)) {
                    $data = array();
                }
                $list[] = array_merge(array(
                    'channel' => $channel,
                    'to_id' => $user['openId'],
                ), $data);
            }

            $this->sendWeChatNotification($key, $logName, $list);
        }
    }

    protected function sendWeChatNotification($key, $logName, $list)
    {
        try {
            $result = $this->getCloudNotificationClient()->sendNotifications($list);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", array('error' => $e->getMessage()));

            return;
        }

        if (empty($result['sn'])) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, $logName, "发送微信通知失败:template:{$key}", $result);

            return;
        }

        $this->getNotificationService()->createWeChatNotificationRecord($result['sn'], $key, $list[0]['template_args']);
    }

    protected function getCloudNotificationClient()
    {
        $biz = $this->biz;

        return $biz['qiQiuYunSdk.notification'];
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
