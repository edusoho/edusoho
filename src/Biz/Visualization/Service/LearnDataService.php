<?php

namespace Biz\Visualization\Service;

interface LearnDataService
{
    public function sumCourseSetLearnTime($courseSetIds);

    public function findActivityLearnDailyByCourseSetIds($courseSetIds);
}
