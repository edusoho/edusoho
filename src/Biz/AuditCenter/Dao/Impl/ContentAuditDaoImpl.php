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
            ],
            'orderbys' => ['id'],
        ];
    }
}
