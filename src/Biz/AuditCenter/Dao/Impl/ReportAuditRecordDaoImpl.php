<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportAuditRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportAuditRecordDaoImpl extends AdvancedDaoImpl implements ReportAuditRecordDao
{
    protected $table = 'report_audit_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'reportTags' => 'delimiter',
            ],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id'],
        ];
    }
}
