<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ReportService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Course extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $reportType)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $this->getCourseService()->tryManageCourse($courseId);

        switch ($reportType) {
            case 'completion_rate_trend':
                $result = $this->getReportService()->getCompletionRateTrend($courseId, 0);
                break;
            default:
                throw new UnprocessableEntityHttpException();
        }

        return $result;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ReportService
     */
    private function getReportService()
    {
        return $this->service('Course:ReportService');
    }
}