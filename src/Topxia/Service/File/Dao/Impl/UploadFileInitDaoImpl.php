<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileInitDao;

class UploadFileInitDaoImpl extends BaseDao implements UploadFileInitDao
{
    protected $table = 'upload_file_inits';

    public function getFile($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function getFileByGlobalId($globalId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE globalId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($globalId)) ?: null;
    }

    public function addFile(array $file)
    {
        $file['createdTime'] = time();
        $affected            = $this->getConnection()->insert($this->table, $file);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Upload File Init error.');
        }

        return $this->getFile($this->getConnection()->lastInsertId());
    }

    public function updateFile($id, array $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getFile($id);
    }
}
