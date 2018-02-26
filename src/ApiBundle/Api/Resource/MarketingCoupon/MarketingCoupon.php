<?php

namespace ApiBundle\Api\Resource\MarketingCoupon;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
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
     */
    public function add(ApiRequest $request)
    {
        $postData = $request->request->all();

        if (empty($postData['mobile'])) {
            throw new NotFoundHttpException('undefined user');
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($postData['mobile']);

        $isNew = false;
        if (empty($user)) {
            $password = substr($postData['mobile'], mt_rand(0, 4), 6);
            $postData['password'] = $password;
            $apiRequest = new ApiRequest('/api/marketing_user', 'POST', array(), $postData);
            $user = $this->invokeResource($apiRequest);
            $isNew = true;
        }

        $response = $this->getCouponService()->generateMarketingCoupon($user['id'], $postData['price'], $postData['expire_day']);
        if ($isNew) {
            $response['password'] = $password;
        }
        $response['isNew'] = $isNew;

        $response['deadline'] = date('c', $response['deadline']);

        $response = ArrayToolkit::parts($response, array('id', 'code', 'type', 'status', 'rate', 'userId', 'deadline', 'targetType', 'targetId', 'password', 'isNew'));

        return $response;
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
