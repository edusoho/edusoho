<?php

namespace ApiBundle\Api\Resource\MarketingCoupon;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Coupon\CouponException;
use Biz\Coupon\Service\CouponBatchService;
use Biz\Coupon\Service\CouponService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;

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
        $couponSetting = $this->getSettingService()->get('coupon', array('enabled' => 0));
        if (!$couponSetting['enabled']) {
            throw CouponException::SETTING_CLOSE();
        }

        $postData = $request->request->all();

        if (empty($postData['mobile'])) {
            throw CommonException::ERROR_PARAMETER();
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

        if (isset($postData['batch_id']) && isset($postData['batch_token'])) {
            $result = $this->getCouponBatchService()->receiveCoupon($postData['batch_token'], $user['id'], true);
            if (isset($result['code']) && 'failed' == $result['code']) {
                $exceptionMethod = $result['exception']['method'];
                throw $result['exception']['class']::$exceptionMethod();
            }
            $response = $this->getCouponService()->getCoupon($result['id']);
            $response['couponBatch'] = $this->getCouponBatchService()->getBatchByToken($postData['batch_token']);
        } else {
            $response = $this->getCouponService()->generateMarketingCoupon($user['id'], $postData['price'], $postData['expire_day']);
        }

        if ($isNew) {
            $response['password'] = $password;
        }

        $response['isNew'] = $isNew;
        $response['deadline'] = date('c', $response['deadline']);
        $response = ArrayToolkit::parts($response,
            array('id', 'code', 'type', 'status', 'rate', 'userId', 'deadline', 'targetType', 'targetId', 'password', 'isNew', 'couponBatch', 'coupon')
        );

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

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
