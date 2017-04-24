<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\FileGroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FileGroupDaoImpl extends GeneralDaoImpl implements FileGroupDao
{
    protected $table = 'file_group';

    public function declares()
    {
        return array();
    }

    public function getByCode($code)
    {
        return $this->getByFields(array(
            'code' => $code,
        ));
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";

        return $this->db()->fetchAll($sql);
    }
}
