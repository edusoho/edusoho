<?php

namespace Topxia\Service\Card\CardDetailProcessor;

use Topxia\Service\Common\ServiceKernel;

class CouponCardDetailProcessor implements CardDetailProcessor
{
	public function getCardDetailByCardId($id)
	{
		return $this->getCouponService()->getCoupon($id);
	}

	public function getCardsDetailByCardIds($ids,$start,$limit)
	{
		return $this->getCouponService()->getCouponsByIds($ids,$start,$limit);
	}

	protected function getCouponService()
	{
		return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
	}
}