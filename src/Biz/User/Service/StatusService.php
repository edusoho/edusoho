<?php
namespace Biz\User\Service;

interface StatusService
{
    public function publishStatus($status, $deleteOld = true);

    public function findStatusesByUserIds($userIds, $start, $limit);

    public function searchStatuses($conditions, $sort, $start, $limit);

    public function searchStatusesCount($conditions);
}
