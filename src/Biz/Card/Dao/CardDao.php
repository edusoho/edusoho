<?php

namespace Biz\Card\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CardDao extends GeneralDaoInterface
{
    public function getByCardId($cardId);

    public function getByUserId($userId);

    public function updateByCardIdAndCardType($fields, $cardId, $cardType);

    public function getByCardIdAndCardType($cardId, $cardType);

    public function findByUserIdAndCardType($userId, $cardType);

    public function findByCardIds(array $cardIds);
}
