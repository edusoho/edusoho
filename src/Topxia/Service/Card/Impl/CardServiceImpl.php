<?php
namespace Topxia\Service\Card\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Card\CardService;
use Topxia\Common\ArrayToolkit;

class CardServiceImpl extends BaseService implements CardService
{
	public function addCard($card)
	{
		if (!ArrayToolkit::requireds($card, array('cardType','cardId'))) {
			throw $this->createServiceException('缺少必要字段，新创建卡失败！');
		}

		$user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createServiceException('用户未登录，不能新建卡');
        }
        $card['userId'] = $user['id'];
        $card['status'] = 'normal';
        $card['createdTime'] = time();

		if ($card['cardType'] == 'moneyCard') {
			$moneyCard = $this->getMoneyCardService()->getMoneyCard($card['cardId']);
			$card['deadline'] = strtotime($moneyCard['deadline']);
			
		}else {
			$coupon = $this->getCouponService()->searchCoupons(array('id' => $card['cardId']), array('id', 'DESC'), 0, 1);
			$card['deadline'] = $coupon[$card['cardId']]['deadline'];
			//Coupon插件没有提供的getCoupon方法
		}
		return $this->getCardDao()->addCard($card);
		
	}

	protected function getCardDao()
	{
		return $this->createDao('Card.CardDao');
	}

	protected function getCouponService()
	{
		return $this->createService('Coupon:Coupon.CouponService');
	}

	protected function getMoneyCardService()
	{
		return $this->createService('MoneyCard.MoneyCardService');
	}

}