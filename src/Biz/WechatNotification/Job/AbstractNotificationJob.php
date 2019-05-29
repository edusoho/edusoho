<?php

namespace Biz\WeChatNotification\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Biz\System\Service\LogService;
use Biz\WeChat\Service\WeChatService;
use Biz\Course\Service\CourseService;

class AbstractNotificationJob extends AbstractJob
{
    const OFFICIAL_TYPE = 'official';

    const LIMIT_NUM = 1000;

    public function execute()
    {
    }

    protected function sendNotifications($userIds, $templateId, $data, $options = array())
    {
        $subscribedUsers = $this->getWeChatService()->findSubscribedUsersByUserIdsAndType($userIds, self::OFFICIAL_TYPE);
        $batchs = array_chunk($subscribedUsers, self::LIMIT_NUM);
        foreach ($batchs as $batch) {
            $list = array();
            foreach ($batch as $user) {
                $list[] = array_merge(array(
                    'touser' => $user['openId'],
                    'template_id' => $templateId,
                    'data' => $data,
                ), $options);
            }
            $this->getCloudNotificationClient()->sendWechatNotificaion($list);
        }
    }

    protected function getCloudNotificationClient()
    {
        $biz = $this->biz;

        return $biz['wechat.cloud_notification_client'];
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return WeChatService
     */
    private function getWeChatService()
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

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
