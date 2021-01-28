<?php

namespace Biz\Course\Dao;

interface ReportDao
{
    public function findCompleteCourseCountGroupByDate($courseId, $startTime, $endTime);
}
