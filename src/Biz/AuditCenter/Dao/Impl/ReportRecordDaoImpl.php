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
            ],
            'orderbys' => ['id'],
        ];
    }
}
