<?php
namespace Topxia\Service\Card\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Card\CardService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Card\CardDetailProcessor\CardDetailFactory;

class CardServiceImpl extends BaseService implements CardService
{
	public function addCard($card)
	{
		if (!ArrayToolkit::requireds($card, array('cardType','cardId','deadline','userId'))) {
			throw $this->createServiceException('缺少必要字段，新创建卡失败！');
		}

        $card['status'] = 'normal';
        $card['createdTime'] = time();


		return $this->getCardDao()->addCard($card);
		
	}

	// public function searchCards($conditions,$sort,$start,$limit)
	// {

	// }


	public function findCardsByUserIdAndCardType($userId,$cardType)
	{
		if (empty($cardType)) {
			throw $this->createServiceException('缺少必要字段，请明确卡的类型');
		}
		return $this->getCardDao()->findCardsByUserIdAndCardType($userId,$cardType);
	}

	public function findCardsByCardTypeAndCardIds($ids,$cardType)
	{
		$processor = $this->getCardDetailProcessor($cardType);
		$limit = count($ids);
		$cardsDetail = $processor->getCardsDetailByCardIds($ids,0,$limit);

		$cardsDetail = $this ->sortArrayByField($cardsDetail,'deadline');
		var_dump($cardsDetail);

		if ($cardType == 'coupon'){
			$cards = ArrayToolkit::group($cardsDetail,'status');
		} elseif ($cardType == 'moneyCard'){
			$cards == ArrayToolkit::group($cardDetail,'cardStatus');
		} else {
			throw $this->createServiceException('暂时没有更多类型的卡');
		}
		return $cards;
		
	}

	protected function sortArrayByField(array $beforeArray,$field)
	{
		uasort($beforeArray , function ($a,$b) use ($field) {
            if ($a[$field] == $b[$field]) {
                return 0;
            }
            return ($a[$field] < $b[$field]) ? -1 : 1;
	    });

	    return $afterArray = $beforeArray;
	}

	protected function sortArrayByKey(array $beforeArray,$key)
	{
		uksort($beforeArray , function ($a,$b) use ($key) {
			if ($a[$key] == $b[$key]) {
				return 0;
			}
			return ($a[$key] < $b[$key]) ? -1 : 1;
		});
		return $afterArray = $beforeArray;
	}

	protected function getCardDao()
	{
		return $this->createDao('Card.CardDao');
	}

	protected function getCardDetailProcessor($cardType)
	{
		$processor = CardDetailFactory::create($cardType);
		return $processor;
	}



}