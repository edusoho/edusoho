<?php

namespace Biz\Distributor\Service\Impl;

use AppBundle\Common\Exception\RuntimeException;
use DrpPlugin\Biz\DistributionToken\Service\DistributionTokenService;

class DistributorUserServiceImpl extends BaseDistributorServiceImpl
{
    /**
     * @param token 分销平台的token，只能使用一次
     *
     * @return array(
     *                'valid'  => true, //是否可注册，指的是分销平台是否颁发过这个token， 如果为false，则注册的用户不算分销平台用户
     *                )
     */
    public function decodeToken($token)
    {
        $tokenInfo = array(
            'valid' => false,
        );
        try {
            $this->validateExistedToken($token);
            $this->getDistributionTokenService()->parseRedirectToken($token);
            $tokenInfo['valid'] = true;
        } catch (\Exception $e) {
            $this->getLogger()->error('distributor sign error DistributorUserServiceImpl::decodeToken '.$e->getMessage(), array('token' => $token));
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
            $this->getLogger()->error('distributor token already existed DistributorUserServiceImpl::validateExistedToken', array('token' => $token));
            throw new RuntimeException('token already existed');
        }
    }

    /**
     * @return DistributionTokenService
     */
    protected function getDistributionTokenService()
    {
        return $this->createService('DrpPlugin:DistributionToken:DistributionTokenService');
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('drp.plugin.logger');
    }
}
