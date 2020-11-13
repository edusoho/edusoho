<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserStayDailyDao extends AdvancedDaoInterface
{
    public function sumUserPageStayTime($conditions, $timeField);
}
