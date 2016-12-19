<?php
namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserApprovalDao extends GeneralDaoInterface
{
    function getLastestByUserIdAndStatus($userId, $status);

    function findByUserIds($userIds);
}
