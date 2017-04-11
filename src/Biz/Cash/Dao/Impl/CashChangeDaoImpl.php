<?php

namespace Biz\Cash\Dao\Impl;

use Biz\Cash\Dao\CashChangeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashChangeDaoImpl extends GeneralDaoImpl implements CashChangeDao
{
    protected $table = 'cash_change';

    public function getByUserId($userId, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($userId)) ?: null;
    }

    public function waveCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET amount = amount + ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, array($value, $id));
    }

    public function declares()
    {
        return array();
    }
}
