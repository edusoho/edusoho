<?php

namespace Biz\Certificate\Dao\Impl;

use Biz\Certificate\Dao\RecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RecordDaoImpl extends GeneralDaoImpl implements RecordDao
{
    protected $table = 'certificate_record';

    public function findExpiredRecords($certificateId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `certificateId` = ? and expiryTime != 0 and expiryTime < ?; ";

        return $this->db()->fetchAll($sql, [$certificateId, time()]);
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
                'status != :statusNotEqual',
                'targetType = :targetType',
                'status IN (:statuses)',
                'userId = :userId',
                'targetId = :targetId',
                'issueTime >= :issueTimeEgt',
                'issueTime <= :issueTimeElt',
            ],
        ];
    }
}
