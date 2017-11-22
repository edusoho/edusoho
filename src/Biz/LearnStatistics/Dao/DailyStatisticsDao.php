<?php

namespace Biz\LearnStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DailyStatisticsDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
