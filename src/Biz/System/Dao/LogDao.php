<?php

namespace Biz\System\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LogDao extends GeneralDaoInterface
{
    public function analysisLoginNumByTime($startTime, $endTime);

    public function analysisLoginDataByTime($startTime, $endTime);
}
