<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileShareDao;

class UploadFileShareDaoImpl extends BaseDao implements UploadFileShareDao
{
    protected $table = 'upload_files_tag';

    public function get($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function add($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    public function delete($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByFileId($fileId)
    {
        return $this->getConnection()->delete($this->table, array('fileId' => $fileId));
    }

    public function findByFileId($fileId)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE fileId = ？";
        return $this->getConnection()->fetchAll($sql, arrat($fileId));
    }

    public function findByTagId($tagId, $start, $limit)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE tagId = ？limit ?,?";
        return $this->getConnection()->fetchAll($sql, arrat($tagId, $start, $$limit));
    }
}
