<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\DiskFileDao;
    
class DiskFileDaoImpl extends BaseDao implements DiskFileDao
{
    protected $table = 'user_disk_file';

    public function getFile($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addFile(array $file)
    {
        $affected = $this->getConnection()->insert($this->table, $file);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user disk file error.');
        }
        return $this->getFile($this->getConnection()->lastInsertId());
    }
}