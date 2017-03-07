<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileTagDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileTagDaoImpl extends GeneralDaoImpl implements UploadFileTagDao
{
    protected $table = 'upload_files_tag';

    public function deleteByFileId($fileId)
    {
        $sql = "DELETE FROM {$this->table} WHERE fileId = ?";
        $result = $this->db()->executeUpdate($sql, array($fileId));

        return $result;
    }

    public function deleteByTagId($tagId)
    {
        $sql = "DELETE FROM {$this->table} WHERE tagId = ?";
        $result = $this->db()->executeUpdate($sql, array($tagId));

        return $result;
    }

    public function findByFileId($fileId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE fileId = ?";

        return $this->db()->fetchAll($sql, array($fileId));
    }

    public function findByTagId($tagId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tagId = ?";

        return $this->db()->fetchAll($sql, array($tagId));
    }

    public function declares()
    {
        return array();
    }
}
