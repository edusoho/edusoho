<?php
namespace Topxia\Service\Coupon\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Coupon\CouponService;
use Topxia\Common\ArrayToolkit;

class CouponServiceImpl extends BaseService implements CouponService
{
    protected $table = 'coupon';

	public function searchCouponsCount($conditions)
    {	
    	$conditions = array_filter($conditions);
        return $this->getCouponDao()->searchCouponsCount($conditions);
    }

    public function searchCoupons($conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('createdTime', 'DESC');
        } 

        $conditions = array_filter($conditions);
        $coupons = $this->getCouponDao()->searchCoupons($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function deleteCoupon($id)
    {
        if (empty($id)) {
            throw $this->createServiceException(sprintf('优惠卷不存在或已被删除'));
        }
        $this->getCouponDao()->deleteCoupon($id);

        $this->getLogService()->info('coupon', 'delete', "删除优惠卷 {$id})");
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon.CouponDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}