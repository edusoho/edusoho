<?php

namespace Biz\Classroom\Service;

use Biz\Classroom\DateTimeRange;

interface ReportService
{
    public function getStudentTrend($classroomId, DateTimeRange $timeRange);

    public function getStudentDetailList($classroomId, $filterConditions, $sort, $start, $limit);

    public function getStudentDetail($classroomId, $userId);

    public function getStudentDetailCount($classroomId, $filterConditions);

    public function getCourseDetailList($classroomId, $filterConditions, $start, $limit);

    public function getCourseDetailCount($classroomId, $filterConditions);

    public function getCourseLearnDetail($classroomId, $courseId, $filterConditions, $start, $limit);

    public function getCourseLearnDetailCount($classroomId, $courseId, $filterConditions);
}
