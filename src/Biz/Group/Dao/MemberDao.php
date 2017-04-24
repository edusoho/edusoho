<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function findByUserId($userId);

    public function getByGroupIdAndUserId($groupId, $userId);
}
