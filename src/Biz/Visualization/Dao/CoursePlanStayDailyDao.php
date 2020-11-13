<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface CoursePlanStayDailyDao extends AdvancedDaoInterface
{
    public function sumUserPageStayTime($conditions, $timeField);
}
