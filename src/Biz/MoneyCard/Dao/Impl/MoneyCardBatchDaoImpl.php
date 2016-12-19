<?php
namespace Topxia\Service\MoneyCard\Dao\Impl;

use Biz\MoneyCard\Dao\MoneyCardBatchDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Topxia\Service\Common\BaseDao;

class MoneyCardBatchDaoImpl extends GeneralDaoImpl implements MoneyCardBatchDao
{
    protected $table = 'money_card_batch';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys'   => array('id', 'createdTime'),
            'conditions' => array(
                'cardPrefix = :cardPrefix',
                'batchName LIKE :batchName'
            ),
        );
    }

    public function getBatchByToken($token, $locked = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        if ($locked) {
            $sql = $sql." for update";
        }

        return $this->db()->fetchAssoc($sql, array($token)) ?: null;
    }
}