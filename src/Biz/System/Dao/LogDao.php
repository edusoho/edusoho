<?php

namespace Biz\System\Dao;

interface LogDao
{
    public function analysisLoginNumByTime($startTime, $endTime);

    public function analysisLoginDataByTime($startTime, $endTime);
}
