<?php
namespace Topxia\Service\User;

interface StatusService
{
    public function publishStatus($status);

    public function findStatusesByUserIds($userIds, $start, $limit);

    public function findStatusesByUserId($userId, $start, $limit);

    public function findStatusesByUserIdCount($userId);
}