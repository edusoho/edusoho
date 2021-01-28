<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserApprovalDao extends GeneralDaoInterface
{
    public function getLastestByUserIdAndStatus($userId, $status);

    public function findByUserIds($userIds);
}
