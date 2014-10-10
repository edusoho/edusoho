<?php
namespace Topxia\Service\User;

interface StatusService
{
    public function publishStatus($status);

    public function findStatusesByUserIds($userIds, $start, $limit);

    public function findStatusesByUserId($userId,$startTime=null,$endTime=null);

    public function findStatusesByUserIdCount($userId,$startTime=null,$endTime=null);
}