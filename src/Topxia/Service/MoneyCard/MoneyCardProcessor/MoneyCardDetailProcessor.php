<?php

namespace Topxia\Service\MoneyCard\MoneyCardProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Card\DetailProcessor\DetailProcessor;

class MoneyCardDetailProcessor implements DetailProcessor
{
	public function getDetailById($id)
	{
		return $this->getMoneyCardService()->getMoneyCard($id);
	}

	public function getCardDetailsByCardIds($ids)
	{
		return $this->getMoneyCardService()->getMoneyCardByIds($ids);
		//此方法目前插件的远程分支不存在
	}

	protected function getMoneyCardService()
	{
		return ServiceKernel::instance()->createService('MoneyCard.MoneyCardService');
	}
}