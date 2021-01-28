<?php

namespace Biz\Card\Dao\Impl;

use Biz\Card\Dao\CardDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CardDaoImpl extends AdvancedDaoImpl implements CardDao
{
    protected $table = 'card';

    public function getByCardId($cardId)
    {
        return $this->getByFields(array(
            'cardId' => $cardId,
        ));
    }

    public function getByUserId($userId)
    {
        return $this->getByFields(array(
            'userId' => $userId,
        ));
    }

    public function updateByCardIdAndCardType($cardId, $cardType, $fields)
    {
        $this->db()->update($this->table, $fields, array('cardId' => $cardId, 'cardType' => $cardType));

        return $this->getByCardIdAndCardType($cardId, $cardType);
    }

    public function getByCardIdAndCardType($cardId, $cardType)
    {
        return $this->getByFields(array(
            'cardId' => $cardId,
            'cardType' => $cardType,
        ));
    }

    public function findByUserIdAndCardType($userId, $cardType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND cardType = ?";

        return $this->db()->fetchAll($sql, array($userId, $cardType)) ?: array();
    }

    public function findByUserIdAndCardTypeAndStatus($userId, $cardType, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND cardType =? AND status = ?";

        return $this->db()->fetchAll($sql, array($userId, $cardType, $status)) ?: array();
    }

    public function findByCardIds(array $cardIds)
    {
        return $this->findInField('cardId', $cardIds);
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
                'id',
            ),
            'conditions' => array(
                'cardType = :cardType',
                'deadline = :deadline',
                'status = :status',
                'userId = :userId',
                'userId IN ( :userIds)',
                'useTime >= :startDateTime',
                'useTime < :endDateTime',
                'createdTime >= :reciveStartTime',
                'createdTime < :reciveEndTime',
            ),
            'timestamps' => array('createdTime'),
        );
    }
}
