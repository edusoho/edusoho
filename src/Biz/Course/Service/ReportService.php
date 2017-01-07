<?php
namespace Biz\Course\Service;

interface ReportService
{
    public function summary($courseId);

    public function getLateMonthLearndData($courseId);

    public function getCourseTaskLearnStat($courseId);
}
