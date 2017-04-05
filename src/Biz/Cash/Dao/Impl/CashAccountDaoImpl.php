<?php

namespace Biz\Cash\Dao\Impl;

use Biz\Cash\Dao\CashAccountDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CashAccountDaoImpl extends GeneralDaoImpl implements CashAccountDao
{
    protected $table = 'cash_account';

    public function getByUserId($userId, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1".($lock ? ' FOR UPDATE' : '');

        return $this->db()->fetchAssoc($sql, array($userId)) ?: array();
    }

    public function findByUserIds(array $userIds)
    {
        return $this->findInField('userId', $userIds);
    }

    public function waveCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET cash = cash + ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, array($value, $id));
    }

    public function waveDownCashField($id, $value)
    {
        $sql = "UPDATE {$this->table} SET cash = cash - ? WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, array($value, $id));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
            ),
            'orderbys' => array(
                'id',
            ),
        );
    }
}
