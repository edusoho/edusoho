<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MeCashAccount extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
            'status' => $request->query->get('status', '')
        );

        $this->getCouponService()->searchCoupons();
    }

    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }
}