<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;

class LessonPublishNotificationJob extends AbstractNotificationJob
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
        if ($courseSet['parentId']) {
            $members = $this->findClassroomMembers($task, $course);
        } else {
            $members = $this->getCourseMemberService()->searchMembers($conditions, array(), 0, PHP_INT_MAX, array('userId'));
        }
        if (empty($members)) {
            return;
        }

        $teachers = $this->getCourseMemberService()->searchMembers(
            array('courseId' => $course['id'], 'role' => 'teacher', 'isVisible' => 1),
            array('id' => 'asc'),
            0,
            1
        );
        $teacher = $this->getUserService()->getUser($teachers[0]['userId']);

        $userIds = ArrayToolkit::column($members, 'userId');
        $data = array(
            'first' => array('value' => ('live' == $task['type']) ? '同学，您好，课程有新的直播任务发布'.PHP_EOL : '同学，您好，课程有新的任务发布'.PHP_EOL),
            'keyword1' => array('value' => $courseSet['title']),
            'keyword2' => array('value' => ('live' == $courseSet['type']) ? '直播课' : '普通课'),
            'keyword3' => array('value' => $teacher['nickname']),
            'keyword4' => array('value' => ('live' == $task['type']) ? date('Y-m-d H:i', $task['startTime']).PHP_EOL : date('Y-m-d H:i', $task['updatedTime']).PHP_EOL),
            'remark' => array('value' => ('live' == $task['type']) ? '请准时参加' : '请及时前往学习'),
        );
        $options = array('url' => $url, 'type' => 'url');
        $templateData = array(
            'template_id' => $templateId,
            'template_args' => $data,
            'goto' => $options,
        );
        $this->sendNotifications($key, 'wechat_notify_lesson_publish', $userIds, $templateData);
    }

    protected function findClassroomMembers($task, $course)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($task['courseId']);

        if (empty($classroom)) {
            return array();
        }

        if ($course['locked']) {
            $excludeStudents = $this->getCourseMemberService()->searchMembers(
                array('courseId' => $course['parentId'], 'role' => 'student'),
                array(),
                0,
                PHP_INT_MAX
            );
            $excludeStudentIds = ArrayToolkit::column($excludeStudents, 'userId');
        }

        $conditions = array('classroomId' => $classroom['id'], 'role' => 'student');
        if (!empty($excludeStudentIds)) {
            $conditions['excludeUserIds'] = $excludeStudentIds;
        }

        return $this->getClassroomService()->searchMembers($conditions, array(), 0, PHP_INT_MAX);
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
