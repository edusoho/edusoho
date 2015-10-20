<?php
namespace Topxia\Service\CardBag\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class CardBagServiceImpl extends BaseService implements CardBagService
{
	public function addCardToCardBag($card)
	{
		$card = $this->generateCard($card);
		return $this->getCardBagDao()->addCardToCardBag($card);
		
	}
	public function generateCard($card)
	{

		if (!ArrayToolkit::requireds($card, array('cardType','cardId'))) {
			throw $this->createServiceException('缺少必要字段，新创建卡失败！');
		}

		$card = ArrayToolkit::parts($card, array('cardType', 'cardId','deadline', 'categoryId', 'useTime', 'status', 'batchId'));

		if ($card['cardType'] == 'moneyCard') {
			$card['password'] = $conditions['password'];
		} elseif ($card['cardType'] == 'coupon') {
			$card['targetType'] = $conditions['targetType'];
			$card['targetId'] = $conditions['targetId'];
			$card['couponType'] = $conditions['couponType'];
			$card['rate'] = $conditions['rate'];
		} else {
			$card['cardType'] = $conditions['cardType'];
			$card['password'] = isset($conditions['password']) ? $conditions['password'] : '0';
			//通用卡字段需要待定
		}

		$card['createTime'] = time();

		return $card;
	}

	protected function getCardBagDao()
	{
		return $this->createDao('cardBag.cardBagDao');
	}

}