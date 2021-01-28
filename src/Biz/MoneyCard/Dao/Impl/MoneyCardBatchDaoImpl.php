<?php

namespace Biz\MoneyCard\Dao\Impl;

use Biz\MoneyCard\Dao\MoneyCardBatchDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MoneyCardBatchDaoImpl extends GeneralDaoImpl implements MoneyCardBatchDao
{
    protected $table = 'money_card_batch';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'cardPrefix = :cardPrefix',
                'batchName LIKE :batchName',
            ),
        );
    }

    public function getBatchByToken($token, array $options = array())
    {
        $lock = isset($options['lock']) && $options['lock'] === true;
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        if ($lock) {
            $sql = $sql.' for update';
        }

        return $this->db()->fetchAssoc($sql, array($token)) ?: null;
    }
}
