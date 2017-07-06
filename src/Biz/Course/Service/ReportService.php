<?php

namespace Biz\Course\Service;

interface ReportService
{
    public function summary($courseId);

    public function summaryNew($courseId);

    public function getLateMonthLearnData($courseId);

    public function getCourseTaskLearnStat($courseId);

    public function getCompletionRateTrend($courseId, $dateRange);

}
