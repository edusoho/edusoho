<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ActivityLearnDailyDao extends AdvancedDaoInterface
{
    public function findByCourseSetIds($courseSetIds);

    public function sumLearnedTimeGroupByTaskIds(array $taskIds);
}
