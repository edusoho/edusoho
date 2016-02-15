<?php

namespace Topxia\Service\Card\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Card\Dao\CardDao;

class CardDaoImpl extends BaseDao implements CardDao
{
    protected $table = 'card';

    public function addCard($card)
    {
        $affected = $this->getConnection()->insert($this->table, $card);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert card error.');
        }

        return $this->getCard($this->getConnection()->lastInsertId());
    }

    public function getCard($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getCardByCardId($cardId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cardId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($cardId)) ?: null;
    }

    public function getCardByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ?: null;
    }

    public function updateCardByCardIdAndCardType($cardId, $cardType, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('cardId' => $cardId, 'cardType' => $cardType));
        return $this->getCardByCardIdAndCardType($cardId, $cardType);
    }

    public function getCardByCardIdAndCardType($cardId, $cardType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cardId = ? AND cardType = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($cardId, $cardType)) ?: null;
    }

    public function findCardsByUserIdAndCardType($userId, $cardType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND cardType = ?";
        return $this->getConnection()->fetchAll($sql, array($userId, $cardType)) ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, 'cardId')
                        ->andWhere('cardType = :cardType')
                        ->andWhere('deadline = :deadline')
                        ->andWhere('status = :status')
                        ->andWhere('userId = :userId');

        return $builder;
    }
}
