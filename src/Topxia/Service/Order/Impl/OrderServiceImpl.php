<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\OrderService;
use Topxia\Common\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{

	public function searchCourseCouponsCount($conditions)
    {	
    	$conditions = array_filter($conditions);
        return $this->getCourseCouponsDao()->searchCourseCouponsCount($conditions);
    }

    public function searchCourseCoupons($conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('createdTime', 'DESC');
        } 

        $conditions = array_filter($conditions);
        $courseCoupons = $this->getCourseCouponsDao()->searchCourseCoupons($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($courseCoupons, 'id');
    }

    public function deleteCoupon($couponId)
    {   
        if (empty($couponId)) {
            throw $this->createServiceException(sprintf('优惠码不存在或已被删除'));
        }
        $this->getCourseCouponsDao()->deleteCoupon($couponId);

        $this->getLogService()->info('coupon', 'delete', "删除优惠码 {$couponId})");
    }

    public function generateCoupon($couponData)
    {   
       
        $coupon['type'] = $couponData['type'];
        $coupon['rate'] = $couponData['rate'];
        $coupon['times'] = $couponData['times'];
        $coupon['code'] = uniqid();
        $coupon['createdTime'] = time();

        $deadline = $couponData['deadline'];
        $time = date("Y-m-d H:i",$coupon['createdTime']);
        $coupon['deadline'] = strtotime("$time +".$deadline." days");

        return $this->getCourseCouponsDao()->generateCoupon($coupon);
    }

    private function getCourseCouponsDao()
    {
        return $this->createDao('Order.CourseCouponsDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}