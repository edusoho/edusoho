<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;

class UploadFileCollectDaoImpl extends BaseDao implements UploadFileCollectDao
{
    protected $table = 'upload_files_collection';

    public function getCollectonByUserIdandFileId($userId, $fileId)
    {
        $that = $this;

        return $this->fetchCached("userId:{$userId}:fileId:{$fileId}", $userId, $fileId, function ($userId, $fileId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? AND fileId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $fileId)) ?: null;
        }

        );
    }

    public function addCollection($collection)
    {
        $affected = $this->getConnection()->insert($this->table, $collection);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert file collection error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function deleteCollection($id)
    {
        $this->getConnection()->delete($this->table, array('id' => $id));
        return true;
    }
}
