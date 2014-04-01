<?php
namespace Coupon\Service\Coupon\Impl;

use Topxia\Service\Common\BaseService;
use Coupon\Service\Coupon\CouponService;
use Topxia\Common\ArrayToolkit;

class CouponServiceImpl extends BaseService implements CouponService
{
    public function getBatch ($id)
    {
        return $this->getCouponBatchDao()->getBatch($id);
    }

    public function findBatchsByIds(array $ids)
    {
        $batchs = $this->getCouponBatchDao()->findBatchsByIds($ids);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function findCouponsByBatchId($batchId, $start, $limit)
    {
        $coupons = $this->getCouponDao()->findCouponsByBatchId($batchId, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCoupons (array $conditions, $orderBy, $start, $limit)
    {
        $coupons = $this->getCouponDao()->searchCoupons($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCouponsCount(array $conditions)
    {
        return $this->getCouponDao()->searchCouponsCount($conditions);
    }

    public function generateCoupon($couponData)
    {   

        $couponData = array_filter($couponData);

        $batchArray = array(
            'name', 
            'prefix', 
            'type', 
            'rate', 
            'generatedNum', 
            'digits', 
            'deadline', 
            'targetType');
        $batch = ArrayToolkit::parts($couponData, $batchArray);
        if (!ArrayToolkit::requireds($couponData, $batchArray)) {
            throw $this->createServiceException("缺少必要参数，生成优惠码失败");
        }

        $batch['deadline'] = strtotime($batch['deadline']);
        $batch['createdTime'] = time();
        if (isset($couponData['targetId'])) {
            $batch['targetId'] = $couponData['targetId'];
        }
        if (isset($couponData['description'])) {
            $batch['description'] = $couponData['description'];
        }
        if ($batch['deadline'] < $batch['createdTime']) {
            throw $this->createServiceException(sprintf('优惠码有效期不能比当前日期晚！'));
        }

        $batch = $this->getCouponBatchDao()->addBatch($batch);

        $this->getCouponDao()->getConnection()->beginTransaction();

        for ($i = 0; $i < $batch['generatedNum']; $i++) { 
            $couponCode = $this->GenerateRandomCode($batch['digits'], $batch['prefix']);
            $coupon = array(
                'code' => $couponCode,
                'type' => $batch['type'],
                'status'=> 'unused',
                'rate' => $batch['rate'],
                'batchId' => $batch['id'],
                'deadline' => $batch['deadline'],
                'targetType' => $batch['targetType'],
                'createdTime' => time()
            );
            $this->getCouponDao()->addCoupon($coupon);
        }

        $this->getCouponDao()->getConnection()->commit();

        $this->getLogService()->info('coupon', 'generate', "生成新批次优惠码,批次前缀为({$batch['prefix']}),批次({$batch['id']})", $batch);

        return $batch;
    }

	public function searchBatchsCount(array $conditions)
    {	
        return $this->getCouponBatchDao()->searchBatchsCount($conditions);
    }

    public function searchBatchs (array $conditions, $orderBy, $start, $limit)
    {
        $batchs = $this->getCouponBatchDao()->searchBatchs($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function deleteBatch($id)
    {
        if (empty($id)) {
            throw $this->createServiceException(sprintf('优惠码批次不存在或已被删除'));
        }

        $this->getCouponDao()->deleteCouponsByBatch($id);
        $this->getCouponBatchDao()->deleteBatch($id);

        $this->getLogService()->info('coupon', 'batch_delete', "删除优惠码批次({$id})");
    }

    public function checkBatchPrefix($prefix)
    {
        if (empty($prefix)) {
            return false;
        }
        $prefix = $this->getCouponBatchDao()->findBatchByPrefix($prefix);
        return empty($prefix) ? true : false;
    }

    public function checkCouponUseable($code, $targetType, $targetId, $amount)
    {
        $coupon = $this->getCouponByCode($code);

        if (empty($coupon)) {
            return array(
                'useable' => 'no',
                'message' => '优惠码不存在'
            );            
        }

        if ($coupon['status'] != 'unused') {
            return array(
                'useable' => 'no',
                'message' => '优惠码已经被使用'
            );
        }

        if ($coupon['deadline'] < time()) {
            return array(
                'useable' => 'no',
                'message' => '优惠码已过期'
            );
        }

        if ($targetType != $coupon['targetType']) {
            return array(
                'useable' => 'no',
                'message' => '优惠码不可用'
            );
        }

        if ($targetId != $coupon['targetId'] && $coupon['targetId'] != 0) {
            return array(
                'useable' => 'no',
                'message' => '优惠码不可用'
            );
        }

        if ($coupon['type'] == 'minus') {
            $decreaseAmount = $coupon['rate'];
            $afterAmount = $amount - $coupon['rate'];
        }

        if ($coupon['type'] == 'discount') {
            $decreaseAmount = $amount * (10 - $coupon['rate']) / 10;
            $afterAmount = $amount * $coupon['rate'] / 10;
        }

        $afterAmount = $afterAmount < 0 ? 0.00 : $afterAmount;
        $decreaseAmount = $afterAmount < 0 ? $amount : $decreaseAmount;

        return array(
            'useable' => 'yes',
            'decreaseAmount' => $decreaseAmount,
            'afterAmount' => $afterAmount
        );
    }

    public function getCouponByCode($code)
    {
        return $this->getCouponDao()->getCouponByCode($code);
    }

    public function useCoupon($code)
    {
        $coupon = $this->getCouponDao()->getCouponByCode($code);
        return $this->getCouponDao()->updateCoupon($coupon['id'], array('status' => 'used'));
    }

    private function GenerateRandomCode ($length, $prefix)
    {
        $randomCode = "";
        for ($j=0; $j < (int)$length; $j++) {
                $randomCode .= mt_rand(0, 9);
        }
        $randomCode = $prefix.$randomCode;

        return $randomCode;
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon:Coupon.CouponDao');
    }

    private function getCouponBatchDao()
    {
        return $this->createDao('Coupon:Coupon.CouponBatchDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}