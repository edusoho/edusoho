<?php

namespace Biz\AuditCenter\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ReportRecordDao extends AdvancedDaoInterface
{
    public function getByAuditIdAndReporter($auditId, $reporter);

    public function getByReporterAndTargetTypeAndTargetId($reporter, $targetType, $targetId);

    public function findByReporterAndTargetTypeAndTargetIds($reporter, $targetType, $targetIds);
}
