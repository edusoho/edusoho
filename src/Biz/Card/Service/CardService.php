<?php

namespace Biz\Card\Service;

interface CardService
{
    const STATUS_RECEIVE = 'receive';

    const TYPE_COUPON = 'coupon';

    public function addCard($card);

    public function batchAddCouponCards(array $userCards);

    public function getCard($id);

    public function getCardByCardId($cardId);

    public function getCardByUserId($userId);

    public function getCardByCardIdAndCardType($cardId, $cardType);

    public function searchCards($conditions, $orderBy, $start, $limit);

    public function updateCardByCardIdAndCardType($cardId, $cardType, $fields);

    public function findCurrentUserAvailableCouponForTargetTypeAndTargetId($targetType, $targetId);

    public function findCardsByUserIdAndCardType($userId, $cardType);

    public function findCardDetailByCardTypeAndCardId($cardType, $id);

    public function findCardDetailsByCardTypeAndCardIds($cardType, $ids);

    public function findCardsByCardIds($cardIds);

    public function sortArrayByField(array $array, $field);

    public function sortArrayByKey(array $array, $key);
}
