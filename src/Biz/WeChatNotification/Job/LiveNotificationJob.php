<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;

class LiveNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $key = $this->args['key'];
        $templateId = $this->getWeChatService()->getTemplateId($key);

        if (empty($templateId)) {
            return;
        }

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
            'first' => array('value' => '同学，您好，您报名的直播课即将开课。'),
            'keyword1' => array('value' => "《{$courseSet['title']}》-《{$task['title']}》"),
            'keyword2' => array('value' => date('Y-m-d H:i', $task['startTime'])),
            'keyword3' => array('value' => '在线直播'),
            'keyword4' => array('value' => '无'),
            'remark' => array('value' => '不要迟到哦。'),
        );
        $options = array('url' => $url, 'type' => 'url');
        $templates = TemplateUtil::templates();
        $templateCode = isset($templates[$key]['id']) ? $templates[$key]['id'] : '';
        $templateData = array(array(
            'template_id' => $templateId,
            'template_code' => $templateCode,
            'template_args' => $data,
            'goto' => $options,
        ));

        $this->sendNotifications($key, 'wechat_notify_live_play', $userIds, $templateData);
    }
}
