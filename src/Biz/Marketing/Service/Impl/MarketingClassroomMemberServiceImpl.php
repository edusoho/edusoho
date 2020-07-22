<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Classroom\Service\Impl\ClassroomServiceImpl;

class MarketingClassroomMemberServiceImpl extends ClassroomServiceImpl
{
    protected function createOrder($goodsSpecsId, $userId, $data, $source = 'outside')
    {
        $classroomProduct = $this->getOrderFacadeService()->getOrderProduct(
            'classroom',
            [
                'targetId' => $goodsSpecsId,
            ]
        );

        $classroomProduct->originPrice = $data['originPrice'];
        $data['targetType'] = 'classroom';
        $params = [
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? [] : $data['deducts'],
            'pay_time' => $data['pay_time'],
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($classroomProduct, $userId, $params, 'Marketing');
    }

    public function createMarketingOrder($classroomId, $userId, $data)
    {
        return $this->createOrder($classroomId, $userId, $data);
    }
}
