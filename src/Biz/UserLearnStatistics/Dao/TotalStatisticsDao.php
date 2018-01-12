<?php

namespace Biz\UserLearnStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TotalStatisticsDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
