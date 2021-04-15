<?php

namespace Biz\AuditCenter\Dao\Impl;

use Biz\AuditCenter\Dao\ContentAuditRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ContentAuditRecordDaoImpl extends AdvancedDaoImpl implements ContentAuditRecordDao
{
    protected $table = 'user_content_audit_record';

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

    /**
     * @param $auditId
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteByAuditId($auditId)
    {
        if (empty($auditId)) {
            return 0;
        }
        $sql = "DELETE FROM {$this->table} WHERE auditId = ?;";

        return $this->db()->executeUpdate($sql, [$auditId]);
    }
}
