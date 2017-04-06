<?php

namespace Biz\Dictionary\Dao\Impl;

use Biz\Dictionary\Dao\DictionaryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DictionaryDaoImpl extends GeneralDaoImpl implements DictionaryDao
{
    protected $table = 'dictionary';

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table} ";

        return $this->db()->fetchAll($sql, array());
    }

    public function declares()
    {
        return array(
        );
    }
}
