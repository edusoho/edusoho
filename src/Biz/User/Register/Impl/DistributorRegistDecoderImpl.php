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
        if ($splitedInfos['valid']) {
            $user['type'] = 'distributor';
        }

        return $user;
    }

    protected function dealDataAfterSave($registration, $user)
    {
        $splitedInfos = $this->splitToken($registration);

        if ($splitedInfos['valid']) {
            $user['token'] = $registration['distributorToken'];
            $this->getDistributorUserService()->createJobData($user);
        }

        if ($splitedInfos['usable']) {
            // 分发优惠券
        }
    }

    /**
     * return \Biz\Distributor\Service\DistributorUserService
     */
    protected function getDistributorUserService()
    {
        return $this->biz->service('Distributor:DistributorUserService');
    }

    /**
     * 分销平台的token，只能使用一次，使用多次，仍然算这个分销商的拉新用户，但不会给奖励
     *
     * @return array(
     *                'coupon' => 123, //优惠券，奖励多少元
     *                'valid'  => true, //是否有效，有效指的是分销平台是否颁发过这个token
     *                'usable' => false //是否有用, 有用指的是，这个token还能不能继续实行奖励
     *                )
     */
    private function splitToken($registration)
    {
        if (empty($this->splitedInfos)) {
            $splitedInfos = array(
                'coupon' => 123,
                'valid' => true,
                'usable' => true,
            );
        }

        return $splitedInfos;
    }
}
