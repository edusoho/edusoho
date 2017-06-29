<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileShareDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileShareDaoImpl extends GeneralDaoImpl implements UploadFileShareDao
{
    protected $table = 'upload_files_share';

    public function findByTargetUserIdAndIsActive($targetUserId, $active = 1)
    {
        $sql = "SELECT DISTINCT sourceUserId FROM {$this->table} WHERE targetUserId = ? AND isActive = ?;";

        return $this->db()->fetchAll($sql, array($targetUserId, $active)) ?: array();
    }

    public function findByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY updatedTime DESC;";

        return $this->db()->fetchAll($sql, array($sourceId)) ?: array();
    }

    public function findActiveByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND isActive = 1 ORDER BY updatedTime DESC;";

        return $this->db()->fetchAll($sql, array($sourceId)) ?: array();
    }

    public function findBySourceUserIdAndTargetUserId($sourceUserId, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND targetUserId = ? LIMIT 1;";

        return $this->db()->fetchAssoc($sql, array($sourceUserId, $targetId)) ?: array();
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'sourceUserId = :sourceUserId',
                'targetUserId = :targetUserId',
                'id = :id',
                'isActive = :isActive',
            ),
            'orderbys' => array(
                'createdTime',
                'updatedTime',
            ),
        );
    }
}
