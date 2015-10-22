<?php
namespace Topxia\Service\Card\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Card\CardService;
use Topxia\Common\ArrayToolkit;

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

	protected function getCardDao()
	{
		return $this->createDao('Card.CardDao');
	}



}