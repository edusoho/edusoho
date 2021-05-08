<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ContentAuditDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ContentAuditDaoImpl extends AdvancedDaoImpl implements ContentAuditDao
{
    protected $table = 'user_content_audit';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'sensitiveWords' => 'delimiter',
            ],
            'conditions' => [
                'id = :id',
                'author = :author',
                'auditor = :auditor',
                'targetType = :targetType',
                'targetType IN (:targetTypes)',
                'status = :status',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'author IN (:authorIds)',
                'auditor IN (:auditorIds)',
                'length(sensitiveWords) <> :containSensitiveWords',
                'length(sensitiveWords) = :notContainSensitiveWords',
                'sensitiveWords LIKE :sensitiveWordsSearch',
                'content LIKE :contentSearch',
            ],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
        ];
    }

    public function getByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getByFields(['targetType' => $targetType, 'targetId' => $targetId]);
    }
}
