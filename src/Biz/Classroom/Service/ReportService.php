<?php

namespace Biz\Classroom\Service;

use Biz\Classroom\DateTimeRange;

interface ReportService
{
    public function getStudentTrend($classroomId, DateTimeRange $timeRange);

    public function getCompletionRateTrend($classroomId, DateTimeRange $timeRange);

    public function getStudentDetail($classroomId, DateTimeRange $timeRange);
}
