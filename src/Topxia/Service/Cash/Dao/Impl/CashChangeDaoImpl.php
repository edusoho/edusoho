<?php

namespace Topxia\Service\Cash\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CashChangeDao;

class CashChangeDaoImpl extends BaseDao implements CashChangeDao
{
    protected $table = 'cash_change';

    public function getChange($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getChangeByUserId($userId, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : '');
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : null;
    }

    public function addChange($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert cash change error.');
        }
        return $this->getChange($this->getConnection()->lastInsertId());
    }

    public function waveCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET amount = amount + ? WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($value, $id));
    }

}