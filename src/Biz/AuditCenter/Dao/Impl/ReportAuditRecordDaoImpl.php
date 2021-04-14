<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportAuditRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportAuditRecordDaoImpl extends AdvancedDaoImpl implements ReportAuditRecordDao
{
    public function declares()
    {
        return [];
    }
}
