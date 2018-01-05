<?php

namespace Biz\User\Register\Impl;

class DistributorRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration)
    {
    }

    protected function dealDataBeforeSave($registration, $user)
    {
        $splitedInfos = $this->splitToken($registration);

        // 有效，则注册来源为分销平台
        if ($splitedInfos['registable']) {
            $user['type'] = 'distributor';
            $user['distributorToken'] = $registration['distributorToken'];
        }

        if ($splitedInfos['rewardable']) {
            // 分发优惠券
        }

        return $user;
    }

    protected function dealDataAfterSave($registration, $user)
    {
        $splitedInfos = $this->splitToken($registration);

        $errMsg = '';
        if ($splitedInfos['registable']) {
            $user['token'] = $registration['distributorToken'];
            $this->getDistributorUserService()->createJobData($user);
        } else {
            $errMsg .= 'not registable ';
        }

        if ($splitedInfos['rewardable']) {
            $this->getCouponService()->generateDistributionCoupon($user['id'], $splitedInfos['couponPrice'], $splitedInfos['couponExpiryDay']);
        } else {
            $errMsg .= 'not rewardable ';
        }

        if (!empty($errMsg)) {
            $this->biz['logger']->error('distributor sign error DistributorRegistDecoderImpl::dealDataAfterSave', array(
                'userId' => $user['id'],
                'token' => $registration['distributorToken'],
            ));
        }
    }

    /**
     * return \Biz\Distributor\Service\DistributorUserService
     *
     * MTIzOjIyMjIxOjEwMDoxNTE1MTE0OTQzOjE1MTUyMDEzNDM6MTI2ODM4ZDdmMDZkYzE4OGQ4YjY0YjExN2EzNmUxZTc6bXpuZDJjZHUyNWxCLUJXQjdPYzNSUnNaT2s4PQ%3D%3D
     */
    protected function getDistributorUserService()
    {
        return $this->biz->service('Distributor:DistributorUserService');
    }

    private function splitToken($registration)
    {
        if (empty($this->splitedInfos)) {
            $this->splitedInfos = $this->getDistributorUserService()->decodeToken($registration['distributorToken']);
        }

        return $this->splitedInfos;
    }

    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
