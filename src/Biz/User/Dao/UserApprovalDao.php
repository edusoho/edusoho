<?php
namespace Biz\User\Dao;

interface UserApprovalDao
{
    function getLastestByUserIdAndStatus($userId, $status);

    function findByUserIds($userIds);
}
