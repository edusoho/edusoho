<?php

namespace Biz\Distributor\Service\Impl;

use AppBundle\Common\Exception\RuntimeException;
use AppBundle\Common\TimeMachine;

class DistributorUserServiceImpl extends BaseDistributorServiceImpl
{
    /**
     * @param token 分销平台的token，只能使用一次
     *
     * @return array(
     *                'couponPrice' => 123, //优惠券，奖励多少分 （单位为分）
     *                'couponExpiryDay' => unix_time, //优惠券有效时间
     *                'valid'  => true, //是否可注册，指的是分销平台是否颁发过这个token， 如果为false，则注册的用户不算分销平台用户
     *                'rewardable' => false  //是否有奖励, 当couponPrice或couponExpiryday=0时, 则注册的用户不会发放优惠券
     *                )
     */
    public function decodeToken($token)
    {
        $splitedStr = explode(':', $token);

        $tokenInfo = array(
            'valid' => false,
            'rewardable' => false,
        );

        try {
            $drpService = $this->getDrpService();
            if (!empty($drpService)) {
                $this->validateExistedToken($token);
                $parsedInfo = $this->getDrpService()->parseRegisterToken($token);
                $tokenInfo['valid'] = true;
                $tokenExpireTime = strtotime('+1 day', intval($parsedInfo['time']));
                if ($tokenExpireTime >= TimeMachine::time()) {
                    $tokenInfo['couponPrice'] = $parsedInfo['coupon_price'];
                    $tokenInfo['couponExpiryDay'] = $parsedInfo['coupon_expiry_day'];
                    if (0 != $tokenInfo['couponPrice'] && 0 != $tokenInfo['couponExpiryDay']) {
                        $tokenInfo['rewardable'] = true;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error DistributorUserServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    public function getSendType($data)
    {
        return 'user';
    }

    public function generateMockedToken($params)
    {
        $data = array(
            'merchant_id' => '123',
            'agency_id' => '22221',
            'coupon_price' => $params['couponPrice'],
            'coupon_expiry_day' => $params['couponExpiryDay'],
        );
        $tokenExpireDateNum = strtotime($params['tokenExpireDateStr']);

        return $this->encodeToken($data, $tokenExpireDateNum);
    }

    protected function convertData($user)
    {
        return array(
           'user_source_id' => $user['id'],
           'nickname' => $user['nickname'],
           'mobile' => $user['verifiedMobile'],
           'registered_time' => $user['createdTime'],
           'token' => $user['token'],
           'updated_time' => $user['updatedTime'],
        );
    }

    protected function getJobType()
    {
        return 'User';
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function validateExistedToken($token)
    {
        $existedUser = $this->getUserService()->searchUsers(
            array('distributorToken' => $token),
            array('id' => 'ASC'),
            0,
            1
        );
        if (!empty($existedUser)) {
            throw new RuntimeException('token already existed');
        }
    }
}
