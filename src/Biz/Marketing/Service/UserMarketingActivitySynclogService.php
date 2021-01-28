<?php

namespace Biz\Marketing\Service;

interface UserMarketingActivitySynclogService
{
    const TARGET_MOBILE = 'mobile';

    const TARGET_ALL = 'all';

    public function createSyncLog($syncLog);

    public function getLastSyncLogByTargetAndTargetValue($target, $targetValue);
}
