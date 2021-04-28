<?php

namespace Biz\WeChatSubscribeNotification\Job;

use AppBundle\Common\ArrayToolkit;

class LiveNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $templateCode = $this->args['templateCode'];
        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateCode);
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
        if ('published' != $course['status']) {
            return;
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ('published' != $courseSet['status']) {
            return;
        }

        $conditions = ['courseId' => $course['id'], 'role' => 'student'];
        $members = $this->getCourseMemberService()->searchMembers($conditions, [], 0, PHP_INT_MAX, ['userId']);
        if (empty($members)) {
            return;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        $subscribeRecords = $this->getWeChatService()->findOnceSubscribeRecordsByTemplateCodeUserIds($templateCode, $userIds);

        if (empty($subscribeRecords)) {
            return;
        }

        $teachers = $this->getCourseMemberService()->searchMembers(
            ['courseId' => $course['id'], 'role' => 'teacher', 'isVisible' => 1],
            ['id' => 'asc'],
            0,
            1
        );
        $teacher = $this->getUserService()->getUser($teachers[0]['userId']);
        $data = [
            'thing2' => ['value' => $this->plainTextByLength($task['title'], 20)],
            'date5' => ['value' => date('Y-m-d H:i', $task['startTime'])],
            'thing15' => ['value' => $teacher['nickname']],
        ];

        $list = [];
        foreach ($subscribeRecords as $record) {
            $subscribeRecordConditions = [
                'templateCode' => $record['templateCode'],
                'templateType' => $record['templateType'],
                'toId' => $record['toId'],
                'isSend_LT' => 1,
            ];
            $subscribeRecordsCount = $this->getWeChatService()->searchSubscribeRecordCount($subscribeRecordConditions);
            $data['thing7']['value'] = $subscribeRecordsCount > 1 ? '剩'.($subscribeRecordsCount - 1).'次通知，请进入课程学习页订阅' : '无剩余通知，请进入课程学习页订阅';
            $list[] = [
                'template_id' => $templateId,
                'template_args' => $data,
                'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                'to_id' => $record['toId'],
                'goto' => $options = ['url' => $url, 'type' => 'url'],
            ];
        }

        $result = $this->sendNotifications($templateCode, 'wechat_subscribe_notify_live_play', $list);
        if ($result) {
            $this->getWeChatService()->updateSubscribeRecordsByIds(array_column($subscribeRecords, 'id'), ['isSend' => 1]);
        }
    }
}
