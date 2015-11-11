<?php

namespace Topxia\Service\Card;

interface CardService
{
    public function addCard($card);

    public function getCard($id);

    public function updateCardByCardIdAndType($cardId,$cardType,$fields);

    public function findCardsByUserIdAndCardType($userId,$cardType);

    public function findCardDetailsByCardTypeAndCardIds($cardType,$ids);
}