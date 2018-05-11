<?php

namespace Biz\Course\Service;

interface ReportService
{
    public function summary($courseId);

    public function getCompletionRateTrend($courseId, $startDate, $endDate);

    public function getStudentTrend($courseId, $timeRange);

    public function searchUserIdsByCourseIdAndFilterAndSortAndKeyword($courseId, $filter, $sort, $start, $limit);

    public function getStudentDetail($courseId, $userIds);
}
