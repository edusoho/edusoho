<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ContentAuditDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ContentAuditDaoImpl extends AdvancedDaoImpl implements ContentAuditDao
{
    protected $table = 'user_content_audit';

    public function declares()
    {
        $declares['orderbys'] = [
            'createdTime'
        ];

        $declares['conditions'] = [
            'author = :author',
            'auditor = :auditor',
            'targetType = :targetType',
            'status = :status',
            'createdTime >= :startTime',
            'createdTime <= :endTime',
            'author IN (:authorIds)',
            'auditor IN (:auditorIds)',
            'status IN (:variousStatus)',
            'sensitiveWords LIKE :sensitiveWordsSearch',
            'content LIKE :contentSearch',
        ];

        return $declares;
    }
}
