<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileCollectDao;

class UploadFileCollectDaoImpl extends BaseDao implements UploadFileCollectDao
{
    protected $table = 'upload_files_collection';
    public function getCollection($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function getCollectonByUserIdandFileId($userId, $fileId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:fileId:{$fileId}", $userId, $fileId, function ($userId, $fileId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND fileId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $fileId)) ?: null;
        }

        );
    }

    public function findCollectonsByUserIdandFileIds($ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $parmaters = array_merge($ids, array($userId));
        $sql       = "SELECT * FROM {$this->getTable()} WHERE fileId IN ({$marks}) and userId = ? ";
        return $this->getConnection()->fetchAll($sql, $parmaters);
    }

    public function findCollectionsByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE userId = ? ";
        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function addCollection($collection)
    {
        $affected = $this->getConnection()->insert($this->table, $collection);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert file collection error.');
        }

        return $this->getCollection($this->getConnection()->lastInsertId());
    }

    public function deleteCollection($id)
    {
        $this->getConnection()->delete($this->table, array('id' => $id));
        return true;
    }
}
