<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportRecordDaoImpl extends AdvancedDaoImpl implements ReportRecordDao
{
    protected $table = 'report_record';

    public function declares()
    {
        return [
            'timestamps' => [],
            'serializes' => [
                'reportTags' => 'delimiter',
            ],
            'conditions' => [
                'id = :id',
                'auditId = :auditId',
                'reporter = :reporter',
                'targetType = :targetType',
                'targetId = :targetId',
            ],
            'orderbys' => ['id', 'auditTime'],
        ];
    }

    public function getByAuditIdAndReporter($auditId, $reporter)
    {
        return $this->getByFields(['auditId' => $auditId, 'reporter' => $reporter]);
    }

    public function getByReporterAndTargetTypeAndTargetId($reporter, $targetType, $targetId)
    {
        return $this->getByFields(['reporter' => $reporter, 'targetType' => $targetType, 'targetId' => $targetId]);
    }

    public function findByReporterAndTargetTypeAndTargetIds($reporter, $targetType, $targetIds)
    {
        if (empty($targetIds)) {
            return [];
        }
        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $parameters = array_merge([$reporter, $targetType], $targetIds);
        $sql = "SELECT * FROM {$this->table} WHERE reporter = ? AND targetType= ? AND targetId IN ({$marks});";

        return $this->db()->fetchAll($sql, $parameters);
    }
}
