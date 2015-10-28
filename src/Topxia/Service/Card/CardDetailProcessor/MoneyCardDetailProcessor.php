<?php

namespace Topxia\Service\Card\CardDetailProcessor;

use Topxia\Service\Common\ServiceKernel;

class MoneyCardDetailProcessor implements CardDetailProcessor
{
	public function getCardDetailByCardId($id)
	{
		return $this->getMoneyCardService()->getMoneyCard($id);
	}

	public function getCardsDetailByCardIds($ids,$start,$limit)
	{
		return $this->getMoneyCardService()->getMoneyCardByIds($ids,$start,$limit);
		//此方法目前插件的远程分支不存在
	}

	protected function getMonerCardService()
	{
		return ServiceKernel::instance()->createService('MoneyCard.MoneyCardService');
	}
}