<?php

namespace Biz\LearnStatistics\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LearnStatisticsDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
