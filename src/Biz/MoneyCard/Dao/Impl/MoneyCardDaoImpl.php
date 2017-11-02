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
        $marks = str_repeat('?,', count($moneyCardIds) - 1).'?';
        $sql = 'select COUNT(id) from '.$this->table.' where cardId in ('.$marks.')';
        $result = $this->db()->fetchAll($sql, $moneyCardIds);

        return $result[0]['COUNT(id)'] == 0 ? true : false;
    }

    public function updateBatchByCardStatus($identifier, $fields)
    {
        $this->db()->update($this->table, $fields, $identifier);
    }

    public function deleteMoneyCardsByBatchId($id)
    {
        return $this->db()->delete($this->table, array('batchId' => $id));
    }

    public function deleteBatchByCardStatus($fields)
    {
        $sql = 'DELETE FROM '.$this->table.' WHERE batchId = ? AND cardStatus != ?';
        $this->db()->executeUpdate($sql, $fields);
    }
}
