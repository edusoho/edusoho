<?php

namespace ApiBundle\Api\Resource\MarketingCoupon;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Coupon\Service\CouponService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MarketingCoupon extends AbstractResource
{
    /**
     * @param ApiRequest $request
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     * @ResponseFilter(class="ApiBundle\Api\Resource\Coupon\CouponFilter", mode="public")
     */
    public function add(ApiRequest $request)
    {
        $postData = $request->request->all();

        if (empty($postData['mobile'])) {
            throw new NotFoundHttpException('undefined user');
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($postData['mobile']);

        if (empty($user)) {
            $apiRequest = new ApiRequest('/api/marketing_user', 'POST', array(), $postData);
            $user = $this->invokeResource($apiRequest);
        }

        $coupon = $this->getCouponService()->generateMarketingCoupon($user['id'], $postData['price'], $postData['expire_day']);

        return $coupon;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }
}
