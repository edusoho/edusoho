<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ReportAuditDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReportAuditDaoImpl extends AdvancedDaoImpl implements ReportAuditDao
{
    public function declares()
    {
        return [];
    }
}
