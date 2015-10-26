<?php

namespace Topxia\Service\Card\CardDetailProcessor;

use Topxia\Service\Common\ServiceKernel;

class CouponCardDetailProcessor implements CardDetailProcessor
{
	public function getCardDetailByCardId($id)
	{
		return $this->getCouponService()->getCouponById($id);
		//此方法目前插件的远程分支不存在
	}

	public function getCardDetailByCardIds($ids,$orderBy,$start,$limit)
	{
		return $this->getCouponService()->getCouponByIds($ids,$orderBy,$start,$limit);
		//此方法目前插件的远程分支不存在
	}

	protected function getCouponService()
	{
		return ServiceKernel::instance()->createService('Coupon:Coupon.CouponService');
	}
}