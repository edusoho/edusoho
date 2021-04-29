<?php

namespace Biz\AuditCenter\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ReportAuditDao extends AdvancedDaoInterface
{
    public function findByIds(array $ids);

    public function getByTargetTypeAndTargetId($targetType, $targetId);

    public function findByTargetTypeAndTargetId($targetType, $targetId);
}
