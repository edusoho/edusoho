<?php

namespace Biz\Live\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface LiveStatisticsDao extends AdvancedDaoInterface
{
    public function getByLiveIdAndType($liveId, $type);

    public function findByLiveIdsAndType(array $liveIds, $type);
}
