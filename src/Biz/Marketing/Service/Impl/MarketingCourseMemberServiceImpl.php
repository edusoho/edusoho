<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Course\Service\Impl\MemberServiceImpl;

class MarketingCourseMemberServiceImpl extends MemberServiceImpl
{
    protected function createOrder($godsSpecsId, $userId, $data)
    {
        $courseProduct = $this->getOrderFacadeService()->getOrderProduct(
            'course',
            [
                'targetId' => $godsSpecsId,
            ]
        );

        $courseProduct->originPrice = $data['originPrice'];
        $data['targetType'] = 'course';
        $params = [
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? [] : $data['deducts'],
            'pay_time' => $data['pay_time'],
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($courseProduct, $userId, $params, 'Marketing');
    }

    public function createMarketingOrder($courseId, $userId, $data)
    {
        return $this->createOrder($courseId, $userId, $data);
    }
}
