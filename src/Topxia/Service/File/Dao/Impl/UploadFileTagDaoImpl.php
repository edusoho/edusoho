<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileTagDao;

class UploadFileTagDaoImpl extends BaseDao implements UploadFileTagDao
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
        $sql    = "DELETE FROM {$this->table} WHERE fileId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($fileId));
        $this->clearCached();
        return $result;
    }

    public function deleteByTagId($tagId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE tagId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($tagId));
        $this->clearCached();
        return $result;
    }
    
    public function findByFileId($fileId)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE fileId = ?";
        return $this->getConnection()->fetchAll($sql, array($fileId));
    }

    public function findByTagId($tagId)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE tagId = ?";
        return $this->getConnection()->fetchAll($sql, array($tagId));
    }
}
