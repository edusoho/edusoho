<?php

namespace Biz\Sms\SmsProcessor;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\UserService;

class TaskSmsProcessor extends BaseSmsProcessor
{
    public function getSmsParams($targetId, $smsType)
    {
        $task = $this->getTaskService()->getTask($targetId);
        if (empty($task)) {
            throw TaskException::NOTFOUND_TASK();
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);

        $task['title'] = StringToolkit::cutter($task['title'], 20, 15, 4);
        $parameters['lesson_title'] = '学习任务：《'.$task['title'].'》';

        if ($task['type'] == 'live') {
            $parameters['startTime'] = date('Y-m-d H:i:s', $task['startTime']);
        }

        $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
        $parameters['course_title'] = '课程：《'.$courseSet['title'].'》';

        global $kernel;
        $site = $this->getSettingService()->get('site');
        $url = empty($site['url']) ? $site['url'] : rtrim($site['url'], ' \/');

        $originUrl = $url . $kernel->getContainer()->get('router')->generate('course_task_show', ['courseId' => $task['courseId'], 'id' => $task['id']]);

        $shortUrl = SmsToolkit::getShortLink($originUrl);
        $url = empty($shortUrl) ? $originUrl : $shortUrl;

        $parameters['url'] = $url;

        return $parameters;
    }

    public function searchUserIds($targetId, $smsType, $start, $limit)
    {
        $task = $this->getTaskService()->getTask($targetId);
        if (empty($task)) {
            throw TaskException::NOTFOUND_TASK();
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);
        if (empty($courseSet['parentId'])) {
            $students = $this->getCourseMemberService()->searchMembers(['courseId' => $task['courseId'], 'role' => 'student'], ['createdTime' => 'Desc'], $start, $limit, ['userId']);
            return ArrayToolkit::column($students, 'userId');
        }

        $classroom = $this->getClassroomService()->getClassroomByCourseId($task['courseId']);

        if (empty($classroom)) {
            return [];
        }
        $course = $this->getCourseService()->getCourse($task['courseId']);
        if ($course['locked']) {
            $excludeStudents = $this->getCourseMemberService()->searchMembers(['courseId' => $course['parentId'], 'role' => 'student'], [], 0, PHP_INT_MAX);
            $excludeStudentIds = ArrayToolkit::column($excludeStudents, 'userId');
        }

        $conditions = ['classroomId' => $classroom['id'], 'role' => 'student'];
        if (!empty($excludeStudentIds)) {
            $conditions['excludeUserIds'] = $excludeStudentIds;
        }

        $students = $this->getClassroomService()->searchMembers($conditions, ['createdTime' => 'ASC'], $start, $limit, ['userId']);
        return ArrayToolkit::column($students, 'userId');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
