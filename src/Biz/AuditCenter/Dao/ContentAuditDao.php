<?php

namespace Biz\AuditCenter\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ContentAuditDao extends AdvancedDaoInterface
{
    public function getByTargetTypeAndTargetId($targetType, $targetId);
}
