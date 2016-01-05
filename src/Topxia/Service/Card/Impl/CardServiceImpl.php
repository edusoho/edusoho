<?php
namespace Topxia\Service\Card\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Card\CardService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Card\DetailProcessor\DetailFactory;

class CardServiceImpl extends BaseService implements CardService
{
    public function addCard($card)
    {
        if (!ArrayToolkit::requireds($card, array('cardType', 'cardId', 'deadline', 'userId'))) {
            throw $this->createServiceException('缺少必要字段，新创建卡失败！');
        }

        $card['createdTime'] = time();

        return $this->getCardDao()->addCard($card);
    }

    public function getCard($id)
    {
        return $this->getCardDao()->getCard($id);
    }

    public function getCardByCardId($cardId)
    {
        return $this->getCardDao()->getCardByCardId($cardId);
    }

    public function getCardByUserId($userId)
    {
        return $this->getCardDao()->getCardByUserId($userId);
    }

    public function getCardByCardIdAndCardType($cardId, $cardType)
    {
        return $this->getCardDao()->getCardByCardIdAndCardType($cardId, $cardType);
    }

    public function updateCardByCardIdAndCardType($cardId, $cardType, $fields)
    {
        return $this->getCardDao()->updateCardByCardIdAndCardType($cardId, $cardType, $fields);
    }

    // public function searchCards($conditions,$sort,$start,$limit)
    // {

    // }

    public function findCardsByUserIdAndCardType($userId, $cardType)
    {
        if (empty($cardType)) {
            throw $this->createServiceException('缺少必要字段，请明确卡的类型');
        }

        return $this->getCardDao()->findCardsByUserIdAndCardType($userId, $cardType);
    }

    public function findCardDetailByCardTypeAndCardId($cardType, $id)
    {
        $processor = $this->getDetailProcessor($cardType);
        return $processor->getDetailById($id);
    }

    public function findCardDetailsByCardTypeAndCardIds($cardType, $ids)
    {
        $processor   = $this->getDetailProcessor($cardType);
        $limit       = count($ids);
        $cardsDetail = $processor->getCardDetailsByCardIds($ids);
        return $cardsDetail;
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

    protected function getCardDao()
    {
        return $this->createDao('Card.CardDao');
    }

    protected function getDetailProcessor($cardType)
    {
        return DetailFactory::create($cardType);
    }
}
