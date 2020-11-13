<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserLearnDailyDao extends AdvancedDaoInterface
{
    public function sumUserLearnTime($conditions, $timeField);

    public function findUserDailyLearnTimeByDate($conditions, $timeField);
}
