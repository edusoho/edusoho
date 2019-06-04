<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;

class LiveNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $key = $this->args['key'];
        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (empty($templateId)) {
            return;
        }

        try {
            $taskId = $this->args['taskId'];
            $url = $this->args['url'];
            $task = $this->getTaskService()->getTask($taskId);
            if ('published' != $task['status']) {
                return;
            }

            $course = $this->getCourseService()->getCourse($task['courseId']);
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            if ('published' != $courseSet['status'] || 'published' != $course['status']) {
                return;
            }

            $conditions = array('courseId' => $course['id'], 'role' => 'student');
            $members = $this->getCourseMemberService()->searchMembers($conditions, array(), 0, PHP_INT_MAX, array('userId'));
            if (empty($members)) {
                return;
            }

            $userIds = ArrayToolkit::column($members, 'userId');
            $data = array(
                'userName' => array('value' => '同学'),
                'courseName' => array('value' => $courseSet['title']),
                'date' => array('value' => $task['startTime']),
                'remark' => array('value' => '不要迟到哦'),
            );
            $options = array('url' => $url);
            $this->sendNotifications($userIds, $templateId, $data, $options);
        } catch (\Exception $e) {
            $this->getLogService()->error(AppLoggerConstant::NOTIFY, 'wechat_notify_live_play', "发送微信通知失败:template:{$key}", array('error' => $e->getMessage()));
        }
    }
}
