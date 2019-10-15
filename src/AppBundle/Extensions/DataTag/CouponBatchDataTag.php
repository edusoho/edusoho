<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Coupon\Service\CouponBatchService;

class CouponBatchDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个课程.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   fetchCourseSet 可选 true | false
     *
     * @param array $arguments 参数
     *
     * @return array 课程
     */
    public function getData(array $arguments)
    {
        if (!isset($arguments['batchId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('batchId参数缺失'));
        }
        $batch = $this->getCouponBatchService()->getBatch($arguments['batchId']);
        if (!empty($batch)) {
            $batch['couponContent'] = $this->getCouponBatchService()->getCouponBatchContent($batch['id']);
        }

        return $batch;
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->createService('Coupon:CouponBatchService');
    }
}
