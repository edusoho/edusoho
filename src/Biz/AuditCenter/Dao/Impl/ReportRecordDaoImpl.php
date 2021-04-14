<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportRecordDaoImpl extends AdvancedDaoImpl implements ReportRecordDao
{
    public function declares()
    {
        return [];
    }
}
