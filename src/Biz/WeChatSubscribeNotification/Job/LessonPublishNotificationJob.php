<?php

namespace Biz\WeChatSubscribeNotification\Job;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Notification\WeChatTemplateMessage\MessageSubscribeTemplateUtil;
use Biz\Sms\SmsType;

class LessonPublishNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $templateCode = $this->args['templateCode'];
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
        if ($courseSet['parentId']) {
            $members = $this->findClassroomMembers($task, $course);
        } else {
            $members = $this->getCourseMemberService()->searchMembers($conditions, [], 0, PHP_INT_MAX, ['userId']);
        }
        if (empty($members)) {
            return;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        $cloudSmsType = 'live' === $task['type'] ? 'sms_live_lesson_publish' : 'sms_normal_lesson_publish';
        $smsTemplateId = 'live' === $task['type'] ? SmsType::LIVE_NOTIFY : SmsType::TASK_PUBLISH;
        $smsParams = 'live' === $task['type'] ? [
            'course_title' => '课程：'.$this->getCourseNameByCourse($course),
            'lesson_title' => '学习任务：'.$task['title'],
            'startTime' => date('Y-m-d H:i', $task['startTime']),
            'url' => $url,
        ] : [
            'course_title' => '课程：'.$this->getCourseNameByCourse($course),
            'lesson_title' => '学习任务：'.$task['title'],
            'url' => $url,
        ];

        $templateId = $this->getWeChatService()->getSubscribeTemplateId($templateCode);
        if (empty($templateId)) {
            return $this->sendSubscribeSmsNotification($userIds, MessageSubscribeTemplateUtil::TEMPLATE_COURSE_UPDATE, $cloudSmsType, $smsTemplateId, $smsParams);
        }

        $subscribeRecords = $this->getWeChatService()->findOnceSubscribeRecordsByTemplateCodeUserIds($templateId, $userIds);
        $smsBatch = $this->sendSubscribeSmsNotification(array_diff($userIds, array_column($subscribeRecords, 'userId')), MessageSubscribeTemplateUtil::TEMPLATE_COURSE_UPDATE, $cloudSmsType, $smsTemplateId, $smsParams);

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
            'thing1' => ['value' => $this->plainTextByLength($this->getCourseNameByCourse($course), 15).'（'.(('live' === $courseSet['type']) ? '直播课' : '普通课').'）'],
            'thing4' => ['value' => $teacher['nickname']],
            'time2' => ['value' => ('live' == $task['type']) ? date('Y-m-d H:i', $task['startTime']) : date('Y-m-d H:i', $task['updatedTime'])],
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
            $data['thing3']['value'] = $subscribeRecordsCount > 1 ? '剩'.($subscribeRecordsCount - 1).'次通知，请进入课程学习页订阅' : '无剩余通知，请进入课程学习页订阅';
            $list[] = [
                'template_id' => $templateId,
                'template_args' => $data,
                'channel' => $this->getWeChatService()->getWeChatSendChannel(),
                'to_id' => $record['toId'],
                'goto' => $options = ['url' => $url, 'type' => 'url'],
            ];
        }
        $result = $this->sendNotifications($templateCode, 'wechat_subscribe_notify_lesson_publish', $list, empty($smsBatch['id']) ? 0 : $smsBatch['id']);

        if ($result) {
            $this->getWeChatService()->updateSubscribeRecordsByIds(array_column($subscribeRecords, 'id'), ['isSend' => 1]);
        }
    }

    protected function findClassroomMembers($task, $course)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($task['courseId']);

        if (empty($classroom)) {
            return [];
        }

        if ($course['locked']) {
            $excludeStudents = $this->getCourseMemberService()->searchMembers(
                ['courseId' => $course['parentId'], 'role' => 'student'],
                [],
                0,
                PHP_INT_MAX
            );
            $excludeStudentIds = ArrayToolkit::column($excludeStudents, 'userId');
        }

        $conditions = ['classroomId' => $classroom['id'], 'role' => 'student'];
        if (!empty($excludeStudentIds)) {
            $conditions['excludeUserIds'] = $excludeStudentIds;
        }

        return $this->getClassroomService()->searchMembers($conditions, [], 0, PHP_INT_MAX);
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
