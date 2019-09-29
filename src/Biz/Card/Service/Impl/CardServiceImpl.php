<?php

namespace Biz\Card\Service\Impl;

use Biz\BaseService;
use Biz\Card\CardException;
use Biz\Card\Dao\CardDao;
use Biz\Card\DetailProcessor\DetailFactory;
use Biz\Card\DetailProcessor\DetailProcessor;
use Biz\Card\Service\CardService;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;

class CardServiceImpl extends BaseService implements CardService
{
    public function addCard($card)
    {
        if (!ArrayToolkit::requireds($card, array('cardType', 'cardId', 'deadline', 'userId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $card['createdTime'] = time();

        return $this->getCardDao()->create($card);
    }

    public function batchAddCouponCards(array $userCards)
    {
        if (empty($userCards)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $userCards = array_values($userCards);

        if (!ArrayToolkit::requireds($userCards[0], array('cardType', 'cardId', 'deadline', 'userId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getCardDao()->batchCreate($userCards);
    }

    public function getCard($id)
    {
        return $this->getCardDao()->get($id);
    }

    public function getCardByCardId($cardId)
    {
        return $this->getCardDao()->getByCardId($cardId);
    }

    public function getCardByUserId($userId)
    {
        return $this->getCardDao()->getByUserId($userId);
    }

    public function getCardByCardIdAndCardType($cardId, $cardType)
    {
        return $this->getCardDao()->getByCardIdAndCardType($cardId, $cardType);
    }

    public function updateCardByCardIdAndCardType($cardId, $cardType, $fields)
    {
        return $this->getCardDao()->updateByCardIdAndCardType($cardId, $cardType, $fields);
    }

    public function searchCards($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareRecordConditions($conditions);

        return $this->getCardDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findCurrentUserAvailableCouponForTargetTypeAndTargetId($targetType, $targetId)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return array();
        }

        $myAvailableCards = $this->getCardDao()->findByUserIdAndCardTypeAndStatus(
            $currentUser['id'],
            CardService::TYPE_COUPON,
            CardService::STATUS_RECEIVE
        );

        $coupons = $this->findCardDetailsByCardTypeAndCardIds(
            CardService::TYPE_COUPON,
            array_column($myAvailableCards, 'cardId')
        );

        foreach ($coupons as $index => $coupon) {
            if (!$this->isAvailable($coupon, $targetType, $targetId)) {
                unset($coupons[$index]);
            }
        }

        return $coupons;
    }

    private function isAvailable($coupon, $targetType, $targetId)
    {
        if ('receive' != $coupon['status']) {
            return false;
        }

        if ($coupon['deadline'] + 86400 < time()) {
            return false;
        }

        if ('all' == $coupon['targetType'] || 'fullDiscount' == $coupon['targetType']) {
            return true;
        }

        if ($coupon['targetType'] != $targetType) {
            return false;
        }

        if (0 == $coupon['targetId']) {
            return true;
        }

        if ('vip' == $targetType && $coupon['targetId'] == $targetId) {
            return true;
        }

        if (in_array($targetType, array('course', 'classroom')) && in_array($targetId, $coupon['targetIds'])) {
            return true;
        }

        return false;
    }

    public function findCardsByUserIdAndCardType($userId, $cardType)
    {
        if (empty($cardType)) {
            $this->createNewException(CardException::TYPE_REQUIRED());
        }

        return $this->getCardDao()->findByUserIdAndCardType($userId, $cardType);
    }

    public function findCardDetailByCardTypeAndCardId($cardType, $id)
    {
        $processor = $this->getDetailProcessor($cardType);

        return $processor->getDetailById($id);
    }

    public function findCardDetailsByCardTypeAndCardIds($cardType, $ids)
    {
        $processor = $this->getDetailProcessor($cardType);
        $cardsDetail = $processor->getCardDetailsByCardIds($ids);

        return $cardsDetail;
    }

    public function findCardsByCardIds($cardIds)
    {
        $cards = $this->getCardDao()->findByCardIds($cardIds);

        return ArrayToolkit::index($cards, 'cardId');
    }

    public function sortArrayByField(array $array, $field)
    {
        uasort($array, function ($a, $b) use ($field) {
            if ($a[$field] == $b[$field]) {
                return 0;
            }

            return ($a[$field] < $b[$field]) ? 1 : -1;
        }
        );

        return $array;
    }

    public function sortArrayByKey(array $array, $key)
    {
        uksort($array, function ($a, $b) use ($key) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }

            return ($a[$key] < $b[$key]) ? 1 : -1;
        }
        );

        return $array;
    }

    private function _prepareRecordConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        if (array_key_exists('nickname', $conditions)) {
            if ($conditions['nickname']) {
                $users = $this->getUserService()->searchUsers(array('nickname' => $conditions['nickname']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
                $conditions['userIds'] = empty($users) ? -1 : ArrayToolkit::column($users, 'id');
            }
        }

        if (isset($conditions['startDateTime'])) {
            $conditions['reciveStartTime'] = $conditions['startDateTime'];
            unset($conditions['startDateTime']);
        }

        if (isset($conditions['endDateTime'])) {
            $conditions['reciveEndTime'] = $conditions['endDateTime'];
            unset($conditions['endDateTime']);
        }

        return $conditions;
    }

    /**
     * @return CardDao
     */
    protected function getCardDao()
    {
        return $this->createDao('Card:CardDao');
    }

    /**
     * @param $cardType
     *
     * @return DetailProcessor
     */
    protected function getDetailProcessor($cardType)
    {
        return DetailFactory::create($cardType);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
