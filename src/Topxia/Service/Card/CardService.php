<?php

namespace Topxia\Service\Card;

interface CardService
{
    public function addCard($card);

    public function findCardsByUserIdAndCardType($userId,$cardType);

    public function findCardDetailsByCardTypeAndCardIds($cardType,$ids);
}