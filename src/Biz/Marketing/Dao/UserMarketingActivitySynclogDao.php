<?php

namespace Biz\Marketing\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserMarketingActivitySynclogDao extends GeneralDaoInterface
{
    public function getLastSyncLogByTargetAndTargetValue($target, $targetValue);
}
