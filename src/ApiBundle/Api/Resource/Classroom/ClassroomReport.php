<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\DateTimeRange;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\ReportService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ClassroomReport extends AbstractResource
{
    public function get(ApiRequest $request, $classroomId, $reportType)
    {
        $classroom = $this->checkClassroom($classroomId);
        switch ($reportType) {
            case 'student_trend':
                $result = $this->getStudentTrend($request, $classroomId);
                break;
//            case 'completion_rate_trend':
//                $result = $this->getCompletionRateTrend($request, $classroomId);
//                break;
//            case 'student_detail':
//                $result = $this->getStudentDetail($request, $classroomId);
//                break;
            default:
                throw new UnprocessableEntityHttpException();
        }

        return $result;
    }

    protected function checkClassroom($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        $this->getClassroomService()->tryHandleClassroom($classroomId);

        return $classroom;
    }

    protected function getStudentTrend($request, $classroomId)
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        return $this->getClassroomReportService()->getStudentTrend($classroomId, new DateTimeRange($startDate, $endDate));
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return ReportService
     */
    protected function getClassroomReportService()
    {
        return $this->biz->service('Classroom:ReportService');
    }
}
