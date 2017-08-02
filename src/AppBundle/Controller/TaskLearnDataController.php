<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

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
                'member' => $member,
            )
        );
    }

    public function studentDetailAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = $request->query->all();

        $conditions = $this->getReportService()->buildStudentDetailConditions($conditions, $courseId);

        $orderBy = $this->getReportService()->buildStudentDetailOrderBy($conditions);

        $studentCount = $this->getCourseMemberService()->countMembers($conditions);
        $paginator = new Paginator(
            $request,
            $studentCount,
            20
        );

        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($members, 'userId');

        list($users, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($courseId, $userIds);

        $taskCount = $this->getTaskService()->countTasks(
            array(
                'courseId' => $courseId,
                'isOptional' => 0,
                'status' => 'published',
            )
        );

        return $this->render('course-manage/overview/task-detail/student-chart-data.html.twig', array(
            'paginator' => $paginator,
            'users' => $users,
            'tasks' => $tasks,
            'members' => $members,
            'taskResults' => $taskResults,
            'course' => $course,
            'taskCount' => $taskCount,
        ));
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
