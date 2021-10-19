<?php

namespace Biz\LiveStatistics\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface LiveMemberStatisticsDao extends AdvancedDaoInterface
{
    public function searchLiveMembersJoinCourseMember($conditions, $start, $limit);
}
