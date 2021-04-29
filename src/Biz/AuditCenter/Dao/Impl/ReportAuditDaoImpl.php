<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportAuditDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportAuditDaoImpl extends AdvancedDaoImpl implements ReportAuditDao
{
    protected $table = 'report_audit';

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getByFields(['targetType' => $targetType, 'targetId' => $targetId]);
    }

    public function findByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->findByFields(['targetType' => $targetType, 'targetId' => $targetId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'reportTags' => 'delimiter',
            ],
            'orderbys' => ['id', 'reportCount'],
            'conditions' => [
                'id IN (:ids)',
                'id = :id',
                'reportTags LIKE :reportTag',
                'status = :status',
                'module = :module',
                'targetType = :targetType',
                'targetId = :targetId',
                'author = :author',
            ],
        ];
    }
}
