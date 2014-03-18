<?php
namespace Topxia\Service\Coupon\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Coupon\CouponService;
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

    public function searchCoupons (array $conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('createdTime', 'DESC');
        } 
        $conditions = array_filter($conditions);

        $coupons = $this->getCouponDao()->searchCoupons($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCouponsCount(array $conditions)
    {
        $conditions = array_filter($conditions);
        return $this->getCouponDao()->searchCouponsCount($conditions);
    }

    public function generateCoupon($couponData)
    {   

        $couponData = array_filter($couponData);
        $batch_array = array(
            'name', 
            'prefix', 
            'type', 
            'rate', 
            'generatedNum', 
            'digits', 
            'deadline', 
            'targetType');
        $batch = ArrayToolkit::parts($couponData, $batch_array);
        if (!ArrayToolkit::requireds($couponData, $batch_array)) {
            throw $this->createServiceException("缺少必要参数，生成优惠码失败");
        }

        $deadline = explode("-", $batch['deadline']);
        $batch['deadline'] = mktime(23,59,59,$deadline[1],$deadline[2],$deadline[0]);
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

        $couponCodes = $this->makeRands($batch['digits'], $batch['generatedNum'], $batch['prefix']);
        $coupons = array();
        foreach ($couponCodes as $couponCode) {
            $coupons[] = array(
                'code' => $couponCode,
                'type' => $batch['type'],
                'status'=> 'unused',
                'rate' => $batch['rate'],
                'batchId' => $batch['id'],
                'deadline' => $batch['deadline'],
                'targetType' => $batch['targetType'],
                'createdTime' => time()
            );
        }
        $coupons = $this->getCouponDao()->addCoupons($coupons);

        $this->getLogService()->info('coupon_batch', 'generate', "生成新批次优惠码,批次前缀为({$batch['prefix']}),批次({$batch['id']})", $batch);

        return $batch;
    }

	public function searchBatchsCount(array $conditions)
    {	
    	$conditions = array_filter($conditions);
        return $this->getCouponBatchDao()->searchBatchsCount($conditions);
    }

    public function searchBatchs (array $conditions, $sort = 'latest', $start, $limit)
    {
        $orderBy = array();
        if ($sort == 'latest') {
            $orderBy =  array('createdTime', 'DESC');
        } 

        $conditions = array_filter($conditions);
        $batchs = $this->getCouponBatchDao()->searchBatchs($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function deleteBatch($id)
    {
        if (empty($id)) {
            throw $this->createServiceException(sprintf('优惠码批次不存在或已被删除'));
        }
        $this->getCouponBatchDao()->deleteBatch($id);
        $this->getCouponDao()->deleteCouponsByBatch($id);

        $this->getLogService()->info('coupon_batch', 'delete', "删除优惠码批次({$id})");
    }

    public function checkPrefix($prefix)
    {
        if (empty($prefix)) {
            return false;
        }
        $prefix = $this->getCouponBatchDao()->findBatchByPrefix($prefix);
        return empty($prefix) ? true : false;
    }

    private function makeRands ($median, $number, $prefix)
    {
        $batchIds = array();
        $i = 0;
        while(true) {
            $id = '';
            for ($j=0; $j < (int)$median; $j++) {
                $id .= mt_rand(0, 9);
            }
            $batchIds[] = $prefix.$id;
            $i++;
            if ($i >= $number) {
                break;
            }
        }
        return $batchIds;
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon.CouponDao');
    }

    private function getCouponBatchDao()
    {
        return $this->createDao('Coupon.CouponBatchDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}