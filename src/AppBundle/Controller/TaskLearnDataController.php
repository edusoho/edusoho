<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Exporter\CourseLiveStatisticExporter;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return $this->forward($config['controller'].':learnDataDetail', [
            'request' => $request,
            'task' => $task,
        ]);
    }

    public function studentDataDetailModalAction(Request $request, $courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
        list($users, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($courseId, [$userId], PHP_INT_MAX);
        $user = reset($users);

        return $this->render('course-manage/overview/task-detail/student-data-modal.html.twig',
            [
                'course' => $course,
                'user' => $user,
                'tasks' => $tasks,
                'taskResults' => $taskResults,
                'member' => $member,
            ]
        );
    }

    public function studentDetailAction(Request $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = $request->query->all();

        $orderBy = $this->getReportService()->buildStudentDetailOrderBy($conditions);
        $conditions = $this->getReportService()->buildStudentDetailConditions($conditions, $courseId);

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
            [
                'courseId' => $courseId,
                'isOptional' => 0,
                'status' => 'published',
            ]
        );

        return $this->render('course-manage/overview/task-detail/student-chart-data.html.twig', [
            'paginator' => $paginator,
            'users' => $users,
            'tasks' => $tasks,
            'members' => $members,
            'taskResults' => $taskResults,
            'course' => $course,
            'taskCount' => $taskCount,
        ]);
    }

    public function taskDetailListAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $page = 20;
        $conditions = [
            'status' => 'published',
            'courseId' => $courseId,
        ];

        $conditions['titleLike'] = $request->query->get('titleLike');

        $taskCount = $this->getTaskService()->countTasks($conditions);
        $paginator = new Paginator(
            $request,
            $taskCount,
            $page
        );

        $tasks = $this->getTaskservice()->searchTasks(
            $conditions,
            ['seq' => 'asc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $tasks = $this->getReportService()->getCourseTaskLearnData($tasks, $course['id']);

        return $this->render('course-manage/overview/task-detail/task-chart-data.html.twig', [
            'course' => $course,
            'paginator' => $paginator,
            'tasks' => $tasks,
        ]);
    }

    public function taskLiveStatisticExportAction(Request $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $exporter = (new CourseLiveStatisticExporter($this->getBiz()));
        $objWriter = $exporter->exporter([
            'taskId' => $task['id'],
            'nameOrMobile' => $request->query->get('nameOrMobile', ''),
        ], 0);
        $response = $this->createStreamedResponse($objWriter);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exporter->getExportFileName(),
            '-'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function createStreamedResponse(\PHPExcel_Writer_IWriter $writer, $status = 200, $headers = [])
    {
        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $status,
            $headers
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

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
