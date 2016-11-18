<?php
namespace Topxia\Service\Course;

interface ReportService
{
    public function summary($courseId);

    public function getLateMonthLearndData($courseId);
}
