<?php

namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Exporter\ClassroomLiveStatisticExporter;
use Biz\Exporter\CourseLiveStatisticExporter;
use Biz\Exporter\TaskLiveStatisticMemberExporter;
use Biz\Exporter\TaskRolCallExporter;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveStatisticExportController extends BaseController
{
    public function classroomLiveStatisticExportAction(Request $request, $classroomId)
    {
        $exporter = new ClassroomLiveStatisticExporter($this->getBiz());
        $objWriter = $exporter->exporter([
            'classroomId' => $classroomId,
            'courseId' => $request->query->get('courseId', ''),
            'title' => $request->query->get('title', ''),
        ], 0);

        return $this->buildExportResponse($exporter, $objWriter);
    }

    public function courseLiveStatisticExportAction(Request $request, $courseId)
    {
        $exporter = (new CourseLiveStatisticExporter($this->getBiz()));
        $objWriter = $exporter->exporter([
            'courseId' => $courseId,
            'title' => $request->query->get('title', ''),
        ], 0);

        return $this->buildExportResponse($exporter, $objWriter);
    }

    public function taskLiveStatisticExportAction(Request $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $exporter = (new TaskLiveStatisticMemberExporter($this->getBiz()));
        $objWriter = $exporter->exporter([
            'taskId' => $task['id'],
            'nameOrMobile' => $request->query->get('nameOrMobile', ''),
        ], 0);

        return $this->buildExportResponse($exporter, $objWriter);
    }

    public function rollCallExportAction(Request $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $exporter = (new TaskRolCallExporter($this->getBiz()));
        $objWriter = $exporter->exporter([
            'taskId' => $task['id'],
            'status' => $request->query->get('status', ''),
        ], 0);

        return $this->buildExportResponse($exporter, $objWriter);
    }

    protected function buildExportResponse($exporter, $objWriter)
    {
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
