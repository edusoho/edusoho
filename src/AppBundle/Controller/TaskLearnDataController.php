<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class TaskLearnDataController extends BaseController
{
    public function learnDataDetailAction(Request $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            return $this->createMessageResponse('error', 'task not found');
        }
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $config = $this->getActivityConfig($task['type']);

        return $this->forward($config['controller'].':learnDataDetail', array(
            'request' => $request,
            'task' => $task,
        ));
    }

    public function studentDataDetailModalAction(Request $request, $courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
        list($users, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($courseId, array($userId), PHP_INT_MAX);
        $user = reset($users);

        return $this->render('course-manage/overview/task-detail/student-data-modal.html.twig',
            array(
                'course' => $course,
                'user' => $user,
                'tasks' => $tasks,
                'taskResults' => $taskResults,
                'member' => $member
            )
        );
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getActivityConfig($type)
    {
        $config = $this->get('extension.manager')->getActivities();

        return $config[$type];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
