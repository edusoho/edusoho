<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ViewLogDao extends GeneralDaoInterface
{
    public function searchGroupByTime($conditions, $startTime, $endTime);
}
