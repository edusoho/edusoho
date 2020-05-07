<?php

namespace Codeages\Biz\ItemBank\Item\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AttachmentDao extends AdvancedDaoInterface
{
    public function getByGlobalId($globalId);

    public function findByTargetIdAndTargetType($targetId, $targetType);

    public function findByTargetIdsAndTargetType($targetIds, $targetType);
}
