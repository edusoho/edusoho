<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareDao;

class UploadFileShareDaoImpl extends BaseDao implements UploadFileShareDao
{
    protected $table = 'upload_files_share';

    public function getShare($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findSharesByTargetUserIdAndIsActive($targetUserId, $active = 1)
    {
        $sql = "SELECT DISTINCT sourceUserId FROM {$this->table} WHERE targetUserId = ? AND isActive = ?;";
        return $this->getConnection()->fetchAll($sql, array($targetUserId, $active)) ?: null;
    }

    public function findShareHistoryByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY updatedTime DESC;";
        return $this->getConnection()->fetchAll($sql, array($sourceId)) ?: null;
    }

    public function findActiveShareHistoryByUserId($sourceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND isActive = 1 ORDER BY updatedTime DESC;";
        return $this->getConnection()->fetchAll($sql, array($sourceId)) ?: null;
    }

    public function findShareHistory($sourceId, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? AND targetUserId = ? LIMIT 1;";
        return $this->getConnection()->fetchAssoc($sql, array($sourceId, $targetId)) ?: null;
    }

    public function addShare($share)
    {
        $affected = $this->getConnection()->insert($this->table, $share);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert file share error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function updateShare($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $id;
    }
}
