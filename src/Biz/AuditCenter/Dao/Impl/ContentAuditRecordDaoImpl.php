<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ContentAuditRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ContentAuditRecordDaoImpl extends AdvancedDaoImpl implements ContentAuditRecordDao
{
    protected $table = 'user_content_audit_record';

    public function declares()
    {
        return [];
    }
}
