<?php

namespace Biz\Card\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CardDao extends GeneralDaoInterface
{
    public function getByCardId($cardId);

    public function getByUserId($userId);

    public function updateByCardIdAndCardType($cardId, $cardType, $fields);

    public function getByCardIdAndCardType($cardId, $cardType);

    public function findByUserIdAndCardType($userId, $cardType);

    public function findByUserIdAndCardTypeAndStatus($userId, $cardType, $status);

    public function findByCardIds(array $cardIds);
}
