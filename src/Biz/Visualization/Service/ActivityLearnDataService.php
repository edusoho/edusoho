<?php

namespace Biz\Visualization\Service;

interface ActivityLearnDataService
{
    public function sumCourseSetLearnTime($courseSetIds);

    public function findActivityLearnDailyByCourseSetIds($courseSetIds);

    public function sumLearnedTimeGroupByTaskIds(array $taskIds);
}
