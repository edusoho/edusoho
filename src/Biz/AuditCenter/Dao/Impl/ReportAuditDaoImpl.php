<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportAuditDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportAuditDaoImpl extends AdvancedDaoImpl implements ReportAuditDao
{
    protected $table = 'report_audit';

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
                'targetType = :targetType',
            ],
        ];
    }
}
