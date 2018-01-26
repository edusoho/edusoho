<?php

namespace Biz\MoneyCard\Dao\Impl;

use Biz\MoneyCard\Dao\MoneyCardDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MoneyCardDaoImpl extends GeneralDaoImpl implements MoneyCardDao
{
    protected $table = 'money_card';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id = :id',
                'rechargeUserId = :rechargeUserId',
                'cardId = :cardId',
                'cardId in ( :cardIds)',
                'cardStatus = :cardStatus',
                'deadline = :deadline',
                'batchId = :batchId',
                'deadline <= :deadlineSearchEnd',
                'deadline >= :deadlineSearchBegin',
                'receiveTime > :receiveTime_GT',
            ),
        );
    }

    public function getMoneyCardByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getMoneyCardByPassword($password)
    {
        return $this->getByFields(array('password' => $password));
    }

    public function isCardIdAvailable($moneyCardIds)
    {
        $result = $this->count(array(
            'cardIds' => $moneyCardIds,
        ));

        return empty($result) ? true : false;
    }

    public function updateBatchByCardStatus($identifier, $fields)
    {
        $this->db()->update($this->table, $fields, $identifier);
    }

    public function deleteMoneyCardsByBatchId($id)
    {
        return $this->db()->delete($this->table, array('batchId' => $id));
    }
}
