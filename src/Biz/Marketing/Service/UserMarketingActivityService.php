<?php

namespace Biz\Marketing\Service;

interface UserMarketingActivityService
{
    public function searchActivities($conditions, $orderBy, $start, $limit);

    public function searchActivityCount($conditions);

    public function syncByMobile($mobile);

    public function findByJoinedIdAndType($joinedId, $type);
}
