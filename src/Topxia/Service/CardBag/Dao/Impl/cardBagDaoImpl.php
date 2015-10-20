<?php

namespace Topxia\Service\CardBag\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Cash\Dao\CardBagDao;


class CashFlowDaoImpl extends BaseDao implements CashFlowDao
{
    protected $table = 'card_bag';

    public function addCardToCardBag($card)
    {
    	$affected = $this->getConnection()->insert($this->table , $card);
    	if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getCard($this->getConnection()->lastInsertId());

    }

    public function getCard($cardId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE cardId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($cardId)) ? : null;
    }

    protected function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'userId')
            ->andWhere('cardId = :cardId')
        return $builder;
    }

}