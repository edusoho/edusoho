<?php

namespace Biz\Certificate\Dao\Impl;

use Biz\Certificate\Dao\RecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class RecordDaoImpl extends AdvancedDaoImpl implements RecordDao
{
    protected $table = 'certificate_record';

    public function findByCertificateId($certificateId)
    {
        return $this->findByFields(['certificateId' => $certificateId]);
    }

    public function findExpiredRecords($certificateId)
    {
        return $this->findByFields(['certificateId' => $certificateId, 'status' => 'expired']);
    }

    public function findByUserIdsAndCertificateId($userIds, $certificateId)
    {
        if (empty($userIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE certificateId = ? AND userId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$certificateId], $userIds));
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime', 'issueTime'],
            'conditions' => [
                'id = :id',
                'certificateId = :certificateId',
                'certificateId IN (:certificateIds)',
                'certificateCode = :certificateCode',
                'status = :status',
                'id NOT IN (:excludeIds)',
                'userId IN (:userIds)',
                'userId = :userId',
                'targetId IN (:targetIds)',
                'status NOT IN (:statusNotIn)',
                'targetType = :targetType',
                'status IN (:statuses)',
                'targetId = :targetId',
                'issueTime >= :issueTimeEgt',
                'issueTime <= :issueTimeElt',
                'expiryTime <= :expiryTime_LE',
                'expiryTime != :expiryTime_NE',
            ],
        ];
    }
}
