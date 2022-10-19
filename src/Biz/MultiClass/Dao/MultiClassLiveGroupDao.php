<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MultiClassLiveGroupDao extends AdvancedDaoInterface
{
    public function getByGroupIdAndLiveId($groupId, $liveId);

    public function findByGroupIds($groupIds);

    public function getByGroupId($groupId);
}
